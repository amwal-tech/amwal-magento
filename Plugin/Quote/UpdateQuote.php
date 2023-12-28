<?php
declare(strict_types=1);

namespace Amwal\Payments\Plugin\Quote;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Amwal\Payments\Model\Checkout\AmwalCheckoutAction;
use Amwal\Payments\Model\Config\Checkout\ConfigProvider;
use Magento\Store\Model\StoreManagerInterface;
use Amwal\Payments\Model\Config;

class UpdateQuote
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * Constructor
     *
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     */
    public function __construct(StoreManagerInterface $storeManager, Config $config)
    {
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * Before submit quote plugin
     * @param QuoteManagement $subject
     * @param Quote $quote
     */
    public function beforeSubmit(QuoteManagement $subject, Quote $quote)
    {
        $store = $this->storeManager->getStore();
        if (!$quote->getData('amwal_order_id')) {
            return;
        }
        $quote->setData(AmwalCheckoutAction::IS_AMWAL_API_CALL, true);
        $quote->getPayment()->setQuote($quote);
        $quote->setPaymentMethod(ConfigProvider::CODE);
        $quote->getPayment()->importData(['method' => ConfigProvider::CODE]);
        $quote->setStoreId($store->getId());

        $quote->setQuoteCurrencyCode($this->config->getCurrencyCode());
        $quote->setStoreCurrencyCode($this->config->getCurrencyCode());
        $quote->setGlobalCurrencyCode($this->config->getCurrencyCode());

        $quote->save();
    }
}
