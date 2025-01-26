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
    private Config $config;
    private OrderUpdate $orderUpdate;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder    $searchCriteriaBuilder,
        LoggerInterface          $logger,
        Config                   $config,
        OrderUpdate              $orderUpdate
    )
    {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->config = $config;
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
            $this->orderUpdate->update($order, 'PendingOrdersUpdate', true);
        }
        $this->logger->notice('Cron Job Finished');
        return $this;
    }

    protected function getPendingOrders(): array
    {
        $toTime = date('Y-m-d H:i:s');
        $fromTime = date('Y-m-d H:i:s', strtotime('-1 hour'));

        $this->logger->notice(sprintf('Searching for orders created between %s and %s', $fromTime, $toTime));

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('created_at', $fromTime, 'gt')
            ->addFilter('created_at', $toTime, 'lt')
            ->addFilter('status', Order::STATE_PENDING_PAYMENT, 'eq')
            ->addFilter('amwal_order_id', true, 'notnull')
            ->create();

        $orders = $this->orderRepository->getList($searchCriteria)->getItems();

        $this->logger->notice(sprintf('Found %s orders', count($orders)));

        return $orders;
    }
}
