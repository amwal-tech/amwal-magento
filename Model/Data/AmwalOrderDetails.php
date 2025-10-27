<?php

declare(strict_types=1);

namespace Amwal\Payments\Model\Data;

use Amwal\Payments\Api\Data\AmwalOrderInterface;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\GetAmwalOrderData;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Amwal\Payments\Model\Data\OrderUpdate;
use RuntimeException;

class AmwalOrderDetails implements AmwalOrderInterface
{
    private OrderRepositoryInterface $orderRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private Request $restRequest;
    private StoreManagerInterface $storeManager;
    private GetAmwalOrderData $getAmwalOrderData;
    private Config $config;
    private OrderUpdate $orderUpdate;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Request $restRequest
     * @param StoreManagerInterface $storeManager
     * @param GetAmwalOrderData $getAmwalOrderData
     * @param Config $config
     * @param OrderUpdate $orderUpdate
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder    $searchCriteriaBuilder,
        Request                  $restRequest,
        StoreManagerInterface    $storeManager,
        GetAmwalOrderData        $getAmwalOrderData,
        Config                   $config,
        OrderUpdate              $orderUpdate
    )
    {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->restRequest = $restRequest;
        $this->storeManager = $storeManager;
        $this->getAmwalOrderData = $getAmwalOrderData;
        $this->config = $config;
        $this->orderUpdate = $orderUpdate;
    }

    /**
     * @param string $amwalOrderId
     * @return array
     */
    public function getOrderDetails($amwalOrderId)
    {
        // Get order by Amwal order ID
        $order = $this->getOrderByAmwalOrderId($amwalOrderId);
        $order->setData('order_url', $this->getOrderUrl($order));
        $order->setData('history', $this->getOrderHistory($order));

        return [
            'order' => $order->getData(),
        ];
    }


    /**
     * @param string $orderId
     * @return array
     */
    public function getOrderByOrderId($orderId)
    {
        // Get order by order ID
        $order = $this->getOrderById($orderId);
        $order->setData('order_url', $this->getOrderUrl($order));
        $order->setData('history', $this->getOrderHistory($order));

        return [
            'order' => $order->getData(),
        ];
    }

    /**
     * @return bool
     */
    public function updateOrderStatus()
    {
        $requestBody = $this->restRequest->getBodyParams();

        $amwalOrderId = $requestBody['amwal_order_id'];
        $orderId = $requestBody['order_id'];
        $refId = $requestBody['ref_id'];

        $order = $this->getOrderByAmwalOrderId($amwalOrderId, $orderId, $refId);
        $amwalOrderData = $this->orderUpdate->update($order, 'AmwalOrderDetails', true);

        if (!$amwalOrderData) {
            return false;
        }
        return true;
    }

    /**
     * @param string $amwalOrderId
     * @param int|null $orderId
     * @param string|null $refId
     * @return OrderInterface
     * @throws RuntimeException
     */
    private function getOrderByAmwalOrderId($amwalOrderId, $orderId = null, $refId = null)
    {
        // Build a search criteria to filter orders by custom attribute
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('amwal_order_id', $amwalOrderId, 'eq');

        if ($orderId) {
            $searchCriteria = $searchCriteria->addFilter('increment_id', $orderId, 'eq');
        }
        if ($refId) {
            $searchCriteria = $searchCriteria->addFilter('ref_id', $refId, 'eq');
        }
        $searchCriteria = $searchCriteria->create();

        // Search for order with the provided custom attribute value and get the order data
        $order = $this->orderRepository->getList($searchCriteria)->getFirstItem();

        if (!$order->getId()) {
            throw new RuntimeException('Order not found, please check the provided Amwal order ID.');
        }
        return $order;
    }


    /**
     * @param string $orderId
     * @return OrderInterface
     * @throws RuntimeException
     */
    private function getOrderById(string $orderId)
    {
        // Build a search criteria to filter orders by custom attribute
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $orderId, 'eq');
        $searchCriteria = $searchCriteria->create();

        // Search for order with the provided custom attribute value and get the order data
        $order = $this->orderRepository->getList($searchCriteria)->getFirstItem();

        if (!$order->getId()) {
            throw new RuntimeException('Order not found, please check the provided Order ID.');
        }
        return $order;
    }

    /**
     * Get order status history
     * @param OrderInterface $order
     * @return array
     */
    private function getOrderHistory(OrderInterface $order): array
    {
        $history = [];
        $statusHistories = $order->getAllStatusHistory();

        foreach ($statusHistories as $statusHistory) {
            $history[] = [
                'status' => $statusHistory->getStatus(),
                'comment' => $statusHistory->getComment(),
                'created_at' => $statusHistory->getCreatedAt(),
                'is_customer_notified' => $statusHistory->getIsCustomerNotified(),
                'is_visible_on_front' => $statusHistory->getIsVisibleOnFront(),
                'entity_name' => $statusHistory->getEntityName()
            ];
        }

        return $history;
    }

    /**
     * @param $order
     * @return string
     */
    private function getOrderUrl($order)
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $orderUrl = $baseUrl . 'sales/order/view/order_id/' . $order->getId();
        return $orderUrl;
    }
}
