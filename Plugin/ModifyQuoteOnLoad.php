<?php

namespace Amwal\Payments\Plugin;


use Amwal\Payments\Model\Checkout\AmwalCheckoutAction;
use Amwal\Payments\Model\Config\Checkout\ConfigProvider;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Quote\Model\Quote;

class ModifyQuoteOnLoad
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        StoreManagerInterface   $storeManager,
        CheckoutSession         $checkoutSession,
        LoggerInterface         $logger
    )
    {
        $this->cartRepository = $cartRepository;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }


    public function afterLoad(Quote $subject, $result)
    {
        $this->logger->info('Quote was modified during load');
        try {
            $this->logger->info('Store ID: ' . $this->storeManager->getStore()->getId());
            $storeId = $this->storeManager->getStore()->getId();
            $quote = $this->checkoutSession->getQuote();
            $quote->setStoreId($storeId);
            $quote->setStoreCurrencyCode($quote->getStore()->getBaseCurrencyCode());
            $quote->setBaseCurrencyCode($quote->getStore()->getBaseCurrencyCode());
            $quote->setQuoteCurrencyCode($quote->getStore()->getBaseCurrencyCode());
            $quote->collectTotals();
            $quote->setCurrency();
            $quote->setData(AmwalCheckoutAction::IS_AMWAL_API_CALL, true);
            $quote->getPayment()->setQuote($quote);
            $quote->setPaymentMethod(ConfigProvider::CODE);
            $quote->getPayment()->importData(['method' => ConfigProvider::CODE]);
            $this->cartRepository->save($quote);

            $this->logger->info('Quote Data: ' . json_encode($quote->getData()));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $result;
    }

}
