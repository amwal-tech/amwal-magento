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

class PendingOrdersUpdate
{
    private OrderRepositoryInterface $orderRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private LoggerInterface $logger;
    private GetAmwalOrderData $getAmwalOrderData;
    private Config $config;
    private OrderNotifier $orderNotifier;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface          $logger,
        Config                   $config,
        GetAmwalOrderData        $getAmwalOrderData,
        OrderNotifier            $orderNotifier
    )
    {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->config = $config;
        $this->getAmwalOrderData = $getAmwalOrderData;
        $this->orderNotifier = $orderNotifier;
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
            $order->setSendEmail(true);
            $this->orderNotifier->notify($order);
            $order->setIsCustomerNotified(true);
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
}
