<?php
declare(strict_types=1);

namespace Amwal\Payments\Cron;

use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\GetAmwalOrderData;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Amwal\Payments\Model\Data\OrderUpdate;

class PendingOrdersUpdate
{
    private OrderRepositoryInterface $orderRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private LoggerInterface $logger;
    private GetAmwalOrderData $getAmwalOrderData;
    private Config $config;
    private OrderUpdate $orderUpdate;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder    $searchCriteriaBuilder,
        LoggerInterface          $logger,
        Config                   $config,
        GetAmwalOrderData        $getAmwalOrderData,
        OrderUpdate              $orderUpdate
    )
    {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->config = $config;
        $this->getAmwalOrderData = $getAmwalOrderData;
        $this->orderUpdate = $orderUpdate;
    }

    /**
     * @throws LocalizedException
     */
    public function execute(): PendingOrdersUpdate
    {
        // check if the cron job is enabled cronjob_enable
        if (!$this->config->isCronJobEnabled()) {
            $this->logger->notice('Cron Job is disabled');
            return $this;
        }
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
                $status = $amwalOrderData['status'];
                $historyComment = __('Successfully completed Amwal payment with transaction ID %1 By Cron Job', $amwalOrderData->getId());

                $this->orderUpdate->update($order, $status, $historyComment);
            }
        }
        $this->logger->notice('Cron Job Finished');
        return $this;
    }

    protected function getPendingOrders(): array
    {
        $fromTime = date('Y-m-d h:i', strtotime('-4 hour'));
        $toTime = date('Y-m-d h:i', strtotime('-1 hour'));
        $this->logger->notice(sprintf('Searching for orders created between %s and %s', $fromTime, $toTime));

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('created_at', $fromTime, 'gt')
            ->addFilter('created_at', $toTime, 'lt')
            ->addFilter('status', [Order::STATE_PENDING_PAYMENT, Order::STATE_CANCELED], 'in')
            ->create();

        $orders = $this->orderRepository->getList($searchCriteria)->getItems();

        $this->logger->notice(sprintf('Found %s orders', count($orders)));
        return $orders;
    }
}
