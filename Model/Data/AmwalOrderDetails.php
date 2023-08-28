<?php

declare(strict_types=1);

namespace Amwal\Payments\Model\Data;

use Amwal\Payments\Api\Data\AmwalOrderInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;

class AmwalOrderDetails implements AmwalOrderInterface
{
    protected $orderRepository;
    protected $searchCriteriaBuilder;
    private Request $restRequest;
    private StoreManagerInterface $storeManager;

    public function __construct(OrderRepositoryInterface $orderRepository, SearchCriteriaBuilder $searchCriteriaBuilder, Request $restRequest, StoreManagerInterface $storeManager)
    {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->restRequest = $restRequest;
        $this->storeManager = $storeManager;
    }

    public function getOrderDetails($amwalOrderId)
    {
        // Get order by Amwal order ID
        $order = $this->getOrderByAmwalOrderId($amwalOrderId);
        $order->setData('order_url', $this->getOrderUrl($order));

        return [
            'order' => $order->getData(),
        ];
    }

    public function updateOrderStatus()
    {
        $requestBody = $this->restRequest->getBodyParams();

        $amwalOrderId = $requestBody['amwal_order_id'];
        $orderId      = $requestBody['order_id'];
        $refId        = $requestBody['ref_id'];
        $status       = $requestBody['status'];

        // Validate the provided status
        if (!$this->isValidStatus($status)) {
            return false;
        }
        try {
            $order = $this->getOrderByAmwalOrderId($amwalOrderId, $orderId, $refId);

            // Update order status
            $order->setStatus($status);
            $order->addStatusHistoryComment('Order status updated to ' . $status . ' by Amwal Payments.');

            // Save the updated order
            $this->orderRepository->save($order);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getOrderByAmwalOrderId($amwalOrderId, $orderId = null, $refId = null)
    {
        // Build a search criteria to filter orders by custom attribute
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('amwal_order_id', $amwalOrderId, 'eq');

        if ($orderId) {
            $searchCriteria = $searchCriteria->addFilter('entity_id', $orderId, 'eq');
        }
        if ($refId) {
            $searchCriteria = $searchCriteria->addFilter('ref_id', $refId, 'eq');
        }
        $searchCriteria =   $searchCriteria->create();

        // Search for order with the provided custom attribute value and get the order data
        $order = $this->orderRepository->getList($searchCriteria)->getFirstItem();

        if (!$order->getId()) {
            throw new \Exception('Order not found, please check the provided Amwal order ID.');
        }
        return $order;
    }

    private function isValidStatus($status)
    {
        $validStatuses = [
            'pending',
            'processing',
            'complete',
            'closed',
            'canceled',
            'holded',
            'payment_review'
        ];

        // Check if the provided status is valid
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        return true;
    }

    private function getOrderUrl($order)
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $orderUrl = $baseUrl . 'sales/order/view/order_id/' . $order->getId();
        return $orderUrl;
    }
}
