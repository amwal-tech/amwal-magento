<?php
declare(strict_types=1);

namespace Amwal\Payments\Cron;

use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\GetAmwalOrderData;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderNotifier;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\Mail\MessageInterface;
use Magento\Store\Model\ScopeInterface;

class PendingOrdersUpdate
{
    private OrderRepositoryInterface $orderRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private LoggerInterface $logger;
    private GetAmwalOrderData $getAmwalOrderData;
    private Config $config;
    private OrderNotifier $orderNotifier;
    private TransportInterfaceFactory $transportFactory;
    private MessageInterface $message;
    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        OrderRepositoryInterface  $orderRepository,
        SearchCriteriaBuilder     $searchCriteriaBuilder,
        LoggerInterface           $logger,
        Config                    $config,
        GetAmwalOrderData         $getAmwalOrderData,
        OrderNotifier             $orderNotifier,
        TransportInterfaceFactory $transportFactory,
        MessageInterface          $message,
        ScopeConfigInterface      $scopeConfig

    )
    {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->config = $config;
        $this->getAmwalOrderData = $getAmwalOrderData;
        $this->orderNotifier = $orderNotifier;
        $this->transportFactory = $transportFactory;
        $this->message = $message;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @throws LocalizedException
     */
    public function execute(): PendingOrdersUpdate
    {
        $this->logger->notice('Starting Cron Job');

        $orders = $this->getPendingOrders();
        foreach ($orders as $order) {
            $amwalOrderId = $order->getAmwalOrderId();
            $orderId = $order->getEntityId();

            if (!$amwalOrderId) {
                $this->logger->error(sprintf('Order %s does not have an Amwal Order ID', $orderId));
                continue;
            }
            $amwalOrderData = $this->getAmwalOrderData->execute($amwalOrderId);
            if ($amwalOrderData && $amwalOrderData['status'] == 'success') {
                $order->setState($this->config->getOrderConfirmedStatus());
                $order->setStatus($this->config->getOrderConfirmedStatus());
                $order->setTotalPaid($order->getGrandTotal());
                $order->addCommentToStatusHistory(__('Successfully completed Amwal payment with transaction ID %1 By Cron Job', $amwalOrderData->getId()));
            } else {
                $order->setState(Order::STATE_CANCELED);
                $order->setStatus(Order::STATE_CANCELED);
                $order->addCommentToStatusHistory(__('Successfully cancelled Amwal payment with transaction ID %1 By Cron Job', $amwalOrderData->getId()));
            }
            $this->sendAdminEmail($order);
            $this->sendCustomerEmail($order);
            $this->orderRepository->save($order);
            $this->logger->notice(sprintf('Order %s has been updated', $orderId));
        }
        $this->logger->notice('Cron Job Finished');
        return $this;
    }

    protected function getPendingOrders(): array
    {
        $fromTime = date('Y-m-d h:i', strtotime('-30 minutes'));
        $this->logger->notice(sprintf('Searching for orders created after %s', $fromTime));
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('created_at', $fromTime, 'gt')
            ->addFilter('status', Order::STATE_PENDING_PAYMENT, 'eq')
            ->create();

        $orders = $this->orderRepository->getList($searchCriteria)->getItems();

        $this->logger->notice(sprintf('Found %s orders', count($orders)));
        return $orders;
    }

    public function sendCustomerEmail($order)
    {
        if ($this->config->isOrderStatusChangedCustomerEmailEnabled()) {
            $order->setSendEmail(true);
            $this->orderNotifier->notify($order);
            $order->setIsCustomerNotified(true);
        }
    }

    public function sendAdminEmail($order)
    {
        if ($this->config->isOrderStatusChangedAdminEmailEnabled()) {
            // Get store email
            $senderEmail = $this->scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE);
            $senderName = $this->scopeConfig->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE);
            $mailContent = 'Order (' . $order->getIncrementId() . ') status has been changed to (' . $order->getStatus() . ') by Amwal Payment Cron Job';

            // Set email content and type
            $this->message->setBody($mailContent);
            $this->message->setFrom($senderEmail);
            $this->message->addTo($senderEmail);
            $this->message->setSubject('Order Status Changed by Amwal Payment Cron Job');
            $this->message->setMessageType(MessageInterface::TYPE_TEXT);

            // Create transport and send the email
            $transport = $this->transportFactory->create(['message' => clone $this->message]);
            $transport->sendMessage();
        }
    }
}
