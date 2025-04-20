<?php
declare(strict_types=1);

namespace Amwal\Payments\Model;

use Amwal\Payments\Model\Config;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;

class HealthCheck
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private SortOrderBuilder $sortOrderBuilder;

    /**
     * Constructor
     *
     * @param Config $config
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        Config $config,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->config = $config;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * Execute health check
     *
     * @return array
     */
    public function execute(): array
    {

        $data = [
            'is_enabled' => $this->config->isActive(),
            'merchant_active' => $this->config->isMerchantValid(),
            'environment' => $this->getEnvironment(),
            'button_position' => [
                'pdp' => $this->config->isExpressCheckoutActive(),
                'cart' => $this->config->isExpressCheckoutActive(),
                'mini_cart' => $this->config->isExpressCheckoutActive(),
                'regular_checkout' => $this->config->isRegularCheckoutActive(),
            ],
            'timestamp' => (new \DateTime())->format(\DateTime::ATOM),
            'last_order' => $this->getLastOrderData(),
        ];

        return [
            'data' => $data
        ];
    }

    /**
     * Get environment
     *
     * @return string
     */
    private function getEnvironment(): string
    {
        $merchantId = $this->config->getMerchantId();

        if (str_contains($merchantId, 'sandbox')) {
            return 'sandbox';
        }

        if (str_contains($merchantId, 'production')) {
            return 'production';
        }

        return 'unknown';
    }

    /**
     * Get the last order data
     *
     * @return array|null
     */
    public function getLastOrderData(): ?array
    {
        $lastOrderData = null;

        try {
            $sortOrder = $this->sortOrderBuilder
                ->setField('created_at')
                ->setDirection('DESC')
                ->create();

            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('amwal_order_id', true, 'notnull')
                ->addSortOrder($sortOrder)
                ->setPageSize(1)
                ->setCurrentPage(1)
                ->create();

            $orders = $this->orderRepository->getList($searchCriteria)->getItems();

            if (!empty($orders)) {
                $order = reset($orders);
                $lastOrderData = [
                    'amwal_order_id' => $order->getAmwalOrderId(),
                    'increment_id' => $order->getIncrementId(),
                    'method' => $order->getPayment()->getMethod(),
                    'created_at' => $order->getCreatedAt(),
                    'grand_total' => $order->getGrandTotal(),
                    'status' => $order->getStatus()
                ];
            }
        } catch (\Throwable $e) {
            // Optionally log this exception
            $lastOrderData = null;
        }

        return $lastOrderData;
    }
}
