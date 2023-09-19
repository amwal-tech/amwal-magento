<?php
declare(strict_types=1);

namespace Amwal\Payments\Cron;

use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\GetAmwalOrderData;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use DateTime as PhpDateTime;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class PendingOrdersUpdate
{
    private OrderRepositoryInterface $orderRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private FilterBuilder $filterBuilder;
    private LoggerInterface $logger;
    private GetAmwalOrderData $getAmwalOrderData;

    private Config $config;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder    $searchCriteriaBuilder,
        FilterBuilder            $filterBuilder,
        LoggerInterface          $logger,
        Config                   $config,
        GetAmwalOrderData        $getAmwalOrderData
    )
    {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->logger = $logger;
        $this->config = $config;
        $this->getAmwalOrderData = $getAmwalOrderData;
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

            if (!$amwalOrderId) {
                $this->logger->error(sprintf('Order %s does not have an Amwal Order ID', $orderId));
                continue;
            }
            $amwalOrderData = $this->getAmwalOrderData->execute($amwalOrderId);

            if (!$amwalOrderData) {
                return false;
            }
            $status = $amwalOrderData['status'];
            if ($status !== 'success') {
                continue;
            }
            $order->setState($this->config->getOrderConfirmedStatus());
            $order->setStatus($this->config->getOrderConfirmedStatus());
            $order->setTotalPaid($order->getGrandTotal());
            $this->orderRepository->save($order);
            $this->logger->notice(sprintf('Order %s has been updated', $orderId));
        }
        $this->logger->notice('Cron Job Finished');
        return $this;
    }

    protected function getPendingOrders(): array
    {
        $currentTime = new PhpDateTime();
        $fromTime = (clone $currentTime)->sub(new \DateInterval('PT2H')); // 2 hours ago

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('created_at', $fromTime->format('Y-m-d H:i:s'), 'gteq')
            ->addFilter('created_at', $currentTime->format('Y-m-d H:i:s'), 'lteq')
            ->addFilter('status', Order::STATE_PENDING_PAYMENT, 'eq')
            ->create();

        $orders = $this->orderRepository->getList($searchCriteria)->getItems();
        return $orders;
    }
}
