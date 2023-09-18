<?php
declare(strict_types=1);

namespace Amwal\Payments\Cron;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use DateTime as PhpDateTime;
use Amwal\Payments\Model\Checkout\PayOrder;
use Psr\Log\LoggerInterface;

class PendingOrdersUpdate
{
    private OrderRepositoryInterface $orderRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private FilterBuilder $filterBuilder;
    private LoggerInterface $logger;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder    $searchCriteriaBuilder,
        FilterBuilder            $filterBuilder,
        LoggerInterface          $logger
    )
    {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->logger = $logger;
    }

    /**
     * @throws LocalizedException
     */
    public function execute(): static
    {
        $this->logger->notice('Starting Cron Job');

        $orders = $this->getPendingOrders();
        foreach ($orders as $order) {
            $amwalOrderId = $order->getAmwalOrderId();
            $orderId = $order->getEntityId();

            $payOrder = new PayOrder();
            $payOrder->execute($orderId, $amwalOrderId);
            $this->logger->notice(sprintf('Order %s has been updated', $orderId));
        }
        $this->logger->notice('Cron Job Finished');
        return $this;
    }

    protected function getPendingOrders(): array
    {
        $currentTime = new PhpDateTime();
        $fromTime = (clone $currentTime)->sub(new \DateInterval('PT2H')); // 2 hours ago
        $toTime = (clone $currentTime)->sub(new \DateInterval('PT30M')); // 30 minutes ago

        $filters[] = $this->filterBuilder
            ->setField('status')
            ->setValue('pending_payment')
            ->setConditionType('eq')
            ->create();

        $filters[] = $this->filterBuilder
            ->setField('created_at')
            ->setValue($fromTime->format('Y-m-d H:i:s'))
            ->setConditionType('gteq')
            ->create();

        $filters[] = $this->filterBuilder
            ->setField('created_at')
            ->setValue($toTime->format('Y-m-d H:i:s'))
            ->setConditionType('lteq')
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters($filters)
            ->create();

        $orders = $this->orderRepository
            ->getList($searchCriteria)
            ->getItems();

        return $orders;
    }
}
