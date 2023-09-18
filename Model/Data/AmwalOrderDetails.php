<?php

declare(strict_types=1);

namespace Amwal\Payments\Model\Data;

use Amwal\Payments\Api\Data\AmwalOrderInterface;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\GetAmwalOrderData;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Amwal\Payments\Model\Checkout\PayOrder;

class AmwalOrderDetails implements AmwalOrderInterface
{
    protected $orderRepository;
    protected $searchCriteriaBuilder;
    private Request $restRequest;
    private StoreManagerInterface $storeManager;
    private GetAmwalOrderData $getAmwalOrderData;
    private Config $config;

    public function __construct(OrderRepositoryInterface $orderRepository, SearchCriteriaBuilder $searchCriteriaBuilder, Request $restRequest, StoreManagerInterface $storeManager, GetAmwalOrderData $getAmwalOrderData, Config $config)
    {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->restRequest = $restRequest;
        $this->storeManager = $storeManager;
        $this->getAmwalOrderData = $getAmwalOrderData;
        $this->config = $config;
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
        $orderId = $requestBody['order_id'];
        $refId = $requestBody['ref_id'];

        $amwalOrderData = $this->getAmwalOrderData->execute($amwalOrderId);
        if (!$amwalOrderData) {
            return false;
        }
        $status = $amwalOrderData['status'];

        if (!$this->isPayValid($amwalOrderId)) {
            return false;
        }

        try {
           $order = $this->getOrderByAmwalOrderId($amwalOrderId, $orderId, $refId);

            // Update order status
            if($status !== 'success'){
                $failure_reason = $amwalOrderData['failure_reason'];
                if(!$failure_reason){
                   return false;
                }
                $order->addStatusHistoryComment('Failure Reason: ' . $failure_reason);
            }else{
                $order->setStatus($this->config->getOrderConfirmedStatus());
                $order->addStatusHistoryComment('Order status updated to ' . $status . ' by Amwal Payments.');
                $order->setTotalPaid($order->getGrandTotal());
            }

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
        $searchCriteria = $searchCriteria->create();

        // Search for order with the provided custom attribute value and get the order data
        $order = $this->orderRepository->getList($searchCriteria)->getFirstItem();

        if (!$order->getId()) {
            throw new \Exception('Order not found, please check the provided Amwal order ID.');
        }
        return $order;
    }

    private function isPayValid($amwalOrderId)
    {
        $order = $this->getOrderByAmwalOrderId($amwalOrderId);
        $orderState = $order->getState();
        $defaultOrderStatus = $this->config->getOrderConfirmedStatus();

        if ($orderState === $defaultOrderStatus) {
            return false;
        }
        return $orderState === 'pending_payment';
    }

    private function getOrderUrl($order)
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $orderUrl = $baseUrl . 'sales/order/view/order_id/' . $order->getId();
        return $orderUrl;
    }
}
