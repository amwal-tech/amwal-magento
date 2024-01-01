<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Data;

use Amwal\Payments\Model\Checkout\AmwalCheckoutAction;
use Amwal\Payments\Model\Config\Checkout\ConfigProvider;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Model\Quote;

class AmwalQuote
{
    protected CartRepositoryInterface $cartRepository;
    protected StoreManagerInterface $storeManager;
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        OrderRepositoryInterface $orderRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->cartRepository = $cartRepository;
        $this->orderRepository = $orderRepository;
        $this->storeManager = $storeManager;
    }
    /**
     * @param $quoteId
     */
    public function getQuote($quoteId)
    {
        $quote = $this->cartRepository->get($quoteId);

        $storeId = $this->storeManager->getStore()->getId();
        $quote->setStoreId($storeId);
        $quote->setStoreCurrencyCode($quote->getStore()->getBaseCurrencyCode());
        $quote->setBaseCurrencyCode($quote->getStore()->getBaseCurrencyCode());
        $quote->setQuoteCurrencyCode($quote->getStore()->getBaseCurrencyCode());
        $quote->setCurrency();
        $quote->collectTotals();
        $quote->setData(AmwalCheckoutAction::IS_AMWAL_API_CALL, true);
        $quote->getPayment()->setQuote($quote);
        $quote->setPaymentMethod(ConfigProvider::CODE);
        $quote->getPayment()->importData(['method' => ConfigProvider::CODE]);

        $this->cartRepository->save($quote);
    }

    /**
     * @param $orderId
     */
    public function getOrder($orderId)
    {
        $order = $this->orderRepository->get($orderId);

        $storeId = $this->storeManager->getStore()->getId();
        $order->setStoreId($storeId);
        $order->setStoreCurrencyCode($order->getStore()->getBaseCurrencyCode());
        $order->setBaseCurrencyCode($order->getStore()->getBaseCurrencyCode());
        $order->setOrderCurrencyCode($order->getStore()->getBaseCurrencyCode());
        $order->setCurrency();
        $order->collectTotals();
        $order->setGrandTotal($order->getBaseGrandTotal());
        $order->setTotalDue($order->getBaseTotalDue());

        $this->orderRepository->save($order);
    }
}
