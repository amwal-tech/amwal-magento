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
use Magento\Framework\DataObject;

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
    private SentryExceptionReport $sentryExceptionReport;

    const FIELD_MAPPINGS = [
        'amwal_order_id' => 'id',
        'ref_id' => 'ref_id',
    ];
    const DEFAULT_CURRENCY_CODE = 'SAR';

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
        SentryExceptionReport     $sentryExceptionReport
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
        $this->sentryExceptionReport = $sentryExceptionReport;
    }

    /**
     * Updates the order based on specified trigger and conditions.
     *
     * @param Order $order Order to be updated.
     * @param string $trigger Type of trigger initiating the update.
     * @param bool $sendAdminEmail Indicates if an admin email should be sent.
     * @return mixed Returns Amwal order data on success, or exception on failure.
     */
    public function update($order, $trigger, $sendAdminEmail = true)
    {
        try {
            $amwalOrderId = $order->getAmwalOrderId();
            if (!$amwalOrderId) {
                throw new \Exception(sprintf('Order %s does not have an Amwal Order ID', $order->getIncrementId()));
            }
            if (strpos($amwalOrderId, '-canceled') !== false) {
                throw new \Exception(sprintf('Skipping Order %s as it was canceled because the payment was retried.', $amwalOrderId));
            }
            $amwalOrderData = $this->getAmwalOrderData->execute($amwalOrderId);
            if (!$amwalOrderData) {
                throw new \Exception(sprintf('Skipping Order %s as it does not exist in Amwal', $amwalOrderId));
            }
            if (!$this->dataValidation($order, $amwalOrderData)) {
                return false;
            }
            if (!$this->isPayValid($order)) {
                return false;
            }

            $status = $amwalOrderData->getStatus();
            if($trigger == 'PendingOrdersUpdate') {
                $historyComment = __('Successfully completed Amwal payment with transaction ID %1 By Cron Job', $amwalOrderId);
            } elseif($trigger == 'AmwalOrderDetails') {
                $historyComment = __('Order status updated to (%1) by Amwal Payments webhook', $status);
            } elseif($trigger == 'PayOrder') {
                $historyComment = __('Successfully completed Amwal payment with transaction ID: %1', $amwalOrderId);
            } else {
                $historyComment = __('Order status updated to (%1) by Amwal Payments', $status);
            }

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
            return $status == 'success'? $amwalOrderData : false;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->sentryExceptionReport->report($e);
            throw $e;
        }
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function isPayValid(OrderInterface $order): bool
    {
        $orderState = $order->getState();
        $defaultOrderStatus = $this->config->getOrderConfirmedStatus();

        if ($orderState === $defaultOrderStatus) {
            return false;
        }
        $validStates = ['pending_payment', 'canceled'];
        if (!in_array($orderState, $validStates)) {
            throw new \Exception(sprintf('Order (%s) is not in a valid state to be updated (%s)', $order->getIncrementId(), $orderState));
        }
        return true;
    }

    private function sendCustomerEmail($order)
    {
        if ($this->config->isOrderStatusChangedCustomerEmailEnabled()) {
            $order->setSendEmail(true);
            $this->orderNotifier->notify($order);
            $order->setIsCustomerNotified(true);
        }
    }

    /**
     * @param OrderInterface $order
     * @param string $subject
     * @param string $message
     */
    private function sendAdminEmail($order, $subject = 'Order Status Changed by Amwal Payment', $message = null)
    {
        if ($this->config->isOrderStatusChangedAdminEmailEnabled()) {
            // Get store email
            $senderEmail = $this->scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE);
            $senderName = $this->scopeConfig->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE);
            $mailContent =  $message ?? __('Order (%1) status has been changed to (%2) by Amwal Payment', $order->getIncrementId(), $order->getStatus());
            // Set email content and type
            $this->message->setBody((string) $mailContent);
            $this->message->setFrom($senderEmail);
            $this->message->addTo($senderEmail);
            $this->message->setSubject($subject);
            $this->message->setMessageType(MessageInterface::TYPE_TEXT);

            // Create transport and send the email
            $transport = $this->transportFactory->create(['message' => clone $this->message]);
            $transport->sendMessage();
        }
    }

    /**
     * @param OrderInterface $order
     * @param string $amwalOrderId
     */
    private function setOrderUrl(OrderInterface $order, $amwalOrderId)
    {
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
            throw new \Exception($message);
        }
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    public function getOrderUrl(OrderInterface $order): string
    {
        return $this->storeManager->getStore()->getBaseUrl() . 'sales/order/view/order_id/' . $order->getEntityId();
    }

    /**
     * Validates the order data.
     *
     * @param Order $order
     * @param DataObject $amwalOrderData
     * @return bool|string True if validation passes, otherwise returns error message.
     */
    public function dataValidation(Order $order, DataObject $amwalOrderData)
    {
        if ($order->getOrderCurrencyCode() != self::DEFAULT_CURRENCY_CODE) {
            $this->sendAdminEmail($order, __('Order (%1) needs Attention', $order->getIncrementId()), $this->dataValidationMessage($order->getIncrementId(), 'order_currency_code', 'default_currency_code', $order->getOrderCurrencyCode(), self::DEFAULT_CURRENCY_CODE));
            throw new \Exception(sprintf('Order (%s) %s does not match Amwal Order %s (%s != %s)', $order->getIncrementId(), 'order_currency_code', 'default_currency_code', $order->getOrderCurrencyCode(), self::DEFAULT_CURRENCY_CODE));
        }
        if ((float)$order->getGrandTotal() != (float)$amwalOrderData->getTotalAmount()) {
            $this->sendAdminEmail($order, __('Order (%1) needs Attention', $order->getIncrementId()), $this->dataValidationMessage($order->getIncrementId(), 'grand_total', 'total_amount', $order->getGrandTotal(), $amwalOrderData->getTotalAmount()));
            throw new \Exception(sprintf('Order (%s) %s does not match Amwal Order %s (%s != %s)', $order->getIncrementId(), 'grand_total', 'total_amount', $order->getGrandTotal(), $amwalOrderData->getTotalAmount()));
        }
        foreach (self::FIELD_MAPPINGS as $orderMethod => $amwalMethod) {
            $orderValue = $order->getData($orderMethod);
            $amwalValue = $amwalOrderData->getData($amwalMethod);
            if ($orderValue != $amwalValue) {
                $this->sendAdminEmail($order, __('Order (%1) needs Attention', $order->getIncrementId()), $this->dataValidationMessage($order->getIncrementId(), $orderMethod, $amwalMethod, $orderValue, $amwalValue));
                throw new \Exception(sprintf('Order (%s) %s does not match Amwal Order %s (%s != %s)', $order->getIncrementId(), $orderMethod, $amwalMethod, $orderValue, $amwalValue));
            }
        }
        return true;
    }

    /*
     * @param string $orderId
     * @param string $orderMethod
     * @param string $amwalMethod
     * @param string $orderValue
     * @param string $amwalValue
     * return string
     */
    private function dataValidationMessage($orderId, $orderMethod, $amwalMethod, $orderValue, $amwalValue)
    {
        return __('Order (%1) Needs Attention, Please check Amwal Order Details in the Sales Order View Page..., Note: Order (%2) %3 does not match Amwal Order %4 (%5 != %6)', $orderId, $orderId, $orderMethod, $amwalMethod, $orderValue, $amwalValue);
    }
}
