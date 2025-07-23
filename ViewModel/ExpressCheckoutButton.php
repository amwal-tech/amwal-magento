<?php
declare(strict_types=1);

namespace Amwal\Payments\ViewModel;

use Amwal\Payments\Model\Config as AmwalConfig;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Checkout\Model\SessionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Context;
use Amwal\Payments\Model\Config\Source\ModuleType;

class ExpressCheckoutButton implements ArgumentInterface
{
    public const TRIGGER_CONTEXT_PRODUCT_LIST = 'product-listing-page';
    public const TRIGGER_CONTEXT_PRODUCT_DETAIL = 'product-detail-page';
    public const TRIGGER_CONTEXT_MINICART = 'minicart';
    public const TRIGGER_CONTEXT_CART = 'cart';
    public const TRIGGER_CONTEXT_LOGIN = 'login';
    public const TRIGGER_CONTEXT_REGULAR_CHECKOUT = 'regular-checkout';
    public const AMWAL_CURRENCY = 'SAR';
    public const CHECKOUT_BUTTON_ID_PREFIX = 'amwal-checkout-button-';
    public const AMWAL_CHECKOUT_BUTTON_ID_PREFIX = 'amwal-checkout';

    /**
     * @var AmwalConfig
     */
    protected AmwalConfig $config;

    /**
     * @var Random
     */
    private Random $random;

    /**
     * @var SessionFactory
     */
    private SessionFactory $checkoutSessionFactory;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Context
     */
    private $context;

    /**
     * @param AmwalConfig $config
     * @param Random $random
     * @param SessionFactory $checkoutSessionFactory
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     */
    public function __construct(
        AmwalConfig $config,
        Random $random,
        SessionFactory $checkoutSessionFactory,
        StoreManagerInterface $storeManager,
        Context $context
    ) {
        $this->config = $config;
        $this->random = $random;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        $this->storeManager = $storeManager;
        $this->context = $context;
    }

    /**
     * @return bool
     */
    public function shouldRender(): bool
    {
        return $this->isExpressCheckoutActive();
    }

    /**
     * @return bool
     */
    public function isExpressCheckoutActive(): bool
    {
        $quote = $this->checkoutSessionFactory->create()->getQuote();

        if ($this->config->getModuleType() === ModuleType::MODULE_TYPE_LITE) {
            return false;
        }

        return $this->config->isActive()
            && $this->config->isExpressCheckoutActive()
            //&& $this->storeManager->getStore()->getCurrentCurrencyCode() == self::AMWAL_CURRENCY
            && ($quote->getItemsCount() == 0 || $quote->getGrandTotal() > 0);
    }

    /**
     * @return bool
     */
    public function shouldHideProceedToCheckout(): bool
    {
        return $this->config->shouldHideProceedToCheckout();
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getUniqueId(): string
    {
        return self::CHECKOUT_BUTTON_ID_PREFIX . '-' . $this->random->getRandomString(8);
    }

    /**
     * @return string
     */
    public function getCheckoutButtonId(): string
    {
        return self::AMWAL_CHECKOUT_BUTTON_ID_PREFIX . '-' . $this->random->getRandomString(8);
    }

    /**
     * @param string|null $formSelector
     * @param ProductInterface|null $product
     * @return string
     */
    public function getFormSelector(?string $formSelector, ?ProductInterface $product): string
    {
        if (!$formSelector) {
            return '';
        }

        if ($product && strpos($formSelector, '%product_id%') !== false) {
            $formSelector = str_replace('%product_id%', (string) $product->getId(), $formSelector);
        }

        return $formSelector;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->config->getLocale();
    }

    /**
     * @return string
     */
    public function getStyleCss(): string
    {
        return $this->config->getStyleCss();
    }

    /**
     * @return string
     */
    public function getStoreCode(): string
    {
        try {
            return $this->storeManager->getStore()->getCode();
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getProductId()
    {
        return $this->context->getParam('id');
    }
}
