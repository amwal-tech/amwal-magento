<?php
declare(strict_types=1);

namespace Amwal\Payments\Cron;

use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\Data\OrderUpdate;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class CanceledOrdersUpdate
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
    public function execute(): CanceledOrdersUpdate
    {
        if (!$this->config->isCronJobEnabled()) {
            $this->logger->notice('Cron Job is disabled');
            return $this;
        }
        $this->logger->notice('[CanceledOrdersUpdate] Starting Cron Job');
        $orders = $this->getCanceledOrders();
        foreach ($orders as $order) {
            try {
                $this->orderUpdate->update($order, 'CanceledOrdersUpdate', true);
            } catch (\Throwable $e) {
                $this->logger->error(sprintf(
                    '[CanceledOrdersUpdate] Error updating order %s: %s',
                    $order->getIncrementId(),
                    $e->getMessage()
                ));
            }
        }
        $this->logger->notice('[CanceledOrdersUpdate] Cron Job Finished');
        return $this;
    }

    protected function getCanceledOrders(): array
    {
        // Use UTC timestamps matching sales_order.created_at storage.
        // Maintain a 15-minute buffer to avoid touching active customer checkout sessions.
        $toTime = gmdate('Y-m-d H:i:s', strtotime('-15 minutes'));
        $fromTime = gmdate('Y-m-d H:i:s', strtotime('-24 hours'));

        $this->logger->notice(sprintf('Searching for orders created between %s and %s (UTC)', $fromTime, $toTime));

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('created_at', $fromTime, 'gt')
            ->addFilter('created_at', $toTime, 'lt')
            ->addFilter('status', Order::STATE_CANCELED, 'eq')
            ->addFilter('amwal_order_id', true, 'notnull')
            ->addFilter('is_amwal_order_canceled', 0, 'eq')
            ->setPageSize(50)
            ->setCurrentPage(1)
            ->create();

        $orders = $this->orderRepository->getList($searchCriteria)->getItems();

        $this->logger->notice(sprintf('Found %s orders', count($orders)));

        return $orders;
    }
}
