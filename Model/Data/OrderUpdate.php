<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Data;

use Amwal\Payments\Model\Checkout\AmwalCheckoutAction;
use Amwal\Payments\Plugin\Sentry\SentryExceptionReport;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\GetAmwalOrderData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderNotifier;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Amwal\Payments\Model\Checkout\InvoiceOrder;
use Psr\Log\LoggerInterface;
use Amwal\Payments\Model\AmwalClientFactory;

class OrderUpdate
{
    private OrderRepositoryInterface $orderRepository;
    private StoreManagerInterface $storeManager;
    private GetAmwalOrderData $getAmwalOrderData;
    private Config $config;
    private OrderNotifier $orderNotifier;
    private TransportInterfaceFactory $transportFactory;
    private MessageInterface $message;
    private ScopeConfigInterface $scopeConfig;
    private InvoiceOrder $invoiceAmwalOrder;
    private LoggerInterface $logger;
    private AmwalClientFactory $amwalClientFactory;
    private SentryExceptionReport $sentryExceptionReportr;

    public function __construct(
        OrderRepositoryInterface  $orderRepository,
        StoreManagerInterface     $storeManager,
        GetAmwalOrderData         $getAmwalOrderData,
        Config                    $config,
        OrderNotifier             $orderNotifier,
        TransportInterfaceFactory $transportFactory,
        MessageInterface          $message,
        ScopeConfigInterface      $scopeConfig,
        InvoiceOrder              $invoiceAmwalOrder,
        LoggerInterface           $logger,
        AmwalClientFactory        $amwalClientFactory,
        SentryExceptionReport     $sentryExceptionReportr
    )
    {
        $this->orderRepository = $orderRepository;
        $this->storeManager = $storeManager;
        $this->getAmwalOrderData = $getAmwalOrderData;
        $this->config = $config;
        $this->orderNotifier = $orderNotifier;
        $this->transportFactory = $transportFactory;
        $this->message = $message;
        $this->scopeConfig = $scopeConfig;
        $this->invoiceAmwalOrder = $invoiceAmwalOrder;
        $this->logger = $logger;
        $this->amwalClientFactory = $amwalClientFactory;
        $this->sentryExceptionReport = $sentryExceptionReportr;
    }

    /*
     * @param OrderRepositoryInterface $orderRepository
     * @param getAmwalOrderData $amwalOrderData
     * @param string $historyComment
     * @param bool $sendAdminEmail
     * return bool
     */
    public function update($order, $amwalOrderData, $historyComment = '', $sendAdminEmail = true)
    {
        if ($order->getAmwalOrderId() != $amwalOrderData->getId()) {
            return false;
        }

        if (!$this->isPayValid($order)) {
            return false;
        }

        try {
            $status = $amwalOrderData->getStatus();

            // Update order status
            if ($status == 'success') {
                $order->setState($this->config->getOrderConfirmedStatus());
                $order->setStatus($this->config->getOrderConfirmedStatus());
                $order->addStatusHistoryComment($historyComment);
                $order->setTotalPaid($order->getGrandTotal());
                $this->setOrderUrl($order, $order->getAmwalOrderId());
                // Send customer email
                $this->sendCustomerEmail($order);

                if($sendAdminEmail) {
                    // Send admin email
                    $this->sendAdminEmail($order);
                }
            } elseif($status == 'fail' && $order->getState() != Order::STATE_CANCELED) {
                $order->setState(Order::STATE_CANCELED);
                $order->setStatus(Order::STATE_CANCELED);
                $order->addStatusHistoryComment('Amwal Transaction Id: ' . $amwalOrderData->getId() . ' has been pending, status: (' . $status . ') and order has been canceled.');
                $order->addStatusHistoryComment('Amwal Transaction Id: ' . $amwalOrderData->getId() . ' Amwal failure reason: ' . $amwalOrderData->getFailureReason());
            }
            // Save the updated order
            $this->orderRepository->save($order);

            if (!$order->hasInvoices() && $status == 'success') {
                $this->invoiceAmwalOrder->execute($order, $amwalOrderData);
            }
            return $status == 'success';
        } catch (\Exception $e) {
            $this->sentryExceptionReport->report($e->getMessage());
            return false;
        }
    }

    private function isPayValid($order)
    {
        $orderState = $order->getState();
        $defaultOrderStatus = $this->config->getOrderConfirmedStatus();

        if ($orderState === $defaultOrderStatus) {
            return false;
        }
        return $orderState === 'pending_payment' || $orderState === 'canceled';
    }

    private function sendCustomerEmail($order)
    {
        if ($this->config->isOrderStatusChangedCustomerEmailEnabled()) {
            $order->setSendEmail(true);
            $this->orderNotifier->notify($order);
            $order->setIsCustomerNotified(true);
        }
    }

    private function sendAdminEmail($order)
    {
        if ($this->config->isOrderStatusChangedAdminEmailEnabled()) {
            // Get store email
            $senderEmail = $this->scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE);
            $senderName = $this->scopeConfig->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE);
            $mailContent = 'Order (' . $order->getIncrementId() . ') status has been changed to (' . $order->getStatus() . ') by Amwal Payment';

            // Set email content and type
            $this->message->setBody($mailContent);
            $this->message->setFrom($senderEmail);
            $this->message->addTo($senderEmail);
            $this->message->setSubject('Order Status Changed by Amwal Payment');
            $this->message->setMessageType(MessageInterface::TYPE_TEXT);

            // Create transport and send the email
            $transport = $this->transportFactory->create(['message' => clone $this->message]);
            $transport->sendMessage();
        }
    }

    /**
     * @param OrderInterface $order
     * @param string $amwalOrderId
     * @return string
     */
    private function setOrderUrl(OrderInterface $order, $amwalOrderId){
        $amwalClient = $this->amwalClientFactory->create();
        $orderDetails = [];
        $orderDetails['order_url'] = $this->getOrderUrl($order);
        try {
            $response = $amwalClient->post(
                'transactions/' . $amwalOrderId . '/set_order_details',
                [
                    RequestOptions::JSON => $orderDetails
                ]
            );
        } catch (GuzzleException $e) {
            $message = sprintf(
                'Unable to set Order details in Amwal for order with ID "%s". Exception: %s',
                $amwalOrderId,
                $e->getMessage()
            );
            $this->logger->error($message);
            $this->sentryExceptionReport->report($e->getMessage());
            return;
        }
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    private function getOrderUrl(OrderInterface $order): string
    {
        return $this->storeManager->getStore()->getBaseUrl() . 'sales/order/view/order_id/' . $order->getEntityId();
    }
}
