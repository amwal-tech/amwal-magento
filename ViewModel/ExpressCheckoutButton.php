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
use Magento\Framework\App\Request\Http;
use Magento\Catalog\Model\ProductRepository;

class ExpressCheckoutButton implements ArgumentInterface
{
    public const TRIGGER_CONTEXT_PRODUCT_LIST = 'product-listing-page';
    public const TRIGGER_CONTEXT_PRODUCT_DETAIL = 'product-detail-page';
    public const TRIGGER_CONTEXT_MINICART = 'minicart';
    public const TRIGGER_CONTEXT_CART = 'cart';
    public const AMWAL_CURRENCY = 'SAR';
    public const CHECKOUT_BUTTON_ID_PREFIX = 'amwal-checkout-button-';


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
     * @var Http
     */
    private Http $request;

    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;


    /**
     * @param AmwalConfig $config
     * @param Random $random
     * @param SessionFactory $checkoutSessionFactory
     * @param StoreManagerInterface $storeManager
     * @param Http $request
     * @param ProductRepository $productRepository
     */
    public function __construct(
        AmwalConfig $config,
        Random $random,
        SessionFactory $checkoutSessionFactory,
        StoreManagerInterface $storeManager,
        Http $request,
        ProductRepository $productRepository
    ) {
        $this->config = $config;
        $this->random = $random;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->productRepository = $productRepository;
    }

    /**
     * @param string $triggerContext
     * @return bool
     */
    public function shouldRender(string $triggerContext): bool
    {
        $shouldRender = $this->isExpressCheckoutActive();

        // Don't render the quick checkout for Product listing or detail if there are already items in the cart.
        try {
            if (in_array($triggerContext, [self::TRIGGER_CONTEXT_PRODUCT_LIST, self::TRIGGER_CONTEXT_PRODUCT_DETAIL]) &&
                $this->checkoutSessionFactory->create()->getQuote()->hasItems()) {
                $shouldRender =  false;
            }
        } catch (NoSuchEntityException|LocalizedException $e) {
            // No need to do anything
        }

        return $shouldRender;
    }

    /**
     * @return bool
     */
    public function isExpressCheckoutActive(): bool
    {
        return !(!$this->config->isActive() || !$this->config->isExpressCheckoutActive() || $this->storeManager->getStore()->getCurrentCurrencyCode() != self::AMWAL_CURRENCY);
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
     * @param string|null $formSelector
     * @param ProductInterface|null $product
     * @return string
     */
    public function getFormSelector(?string $formSelector, ?ProductInterface $product): string
    {
        if (!$formSelector) {
            return '';
        }

        if (strpos($formSelector, '%product_id%') && $product) {
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
     * @param ProductInterface|null $product
     * @return float
     */
    public function getProductDiscount(?ProductInterface $product): float
    {
        $productId = $this->request->getParam('id');
        if ($product) {
            $discountAmount = $this->getDiscountAmount($product);
            return $discountAmount;
        }
        if ($productId) {
            $discountAmount = $this->getDiscountAmount($productId);
            return $discountAmount;
        }
        try {
            $quote = $this->checkoutSessionFactory->create()->getQuote();
            $items = $quote->getAllItems();
            $discountAmount = 0;
            foreach ($items as $item) {
                $discountAmount += $this->getDiscountAmount($item->getProductId());
            }
            return $discountAmount;
        } catch (NoSuchEntityException|LocalizedException $e) {
            return 0;
        }
        return 0;
    }

    /**
     * @param ProductInterface|null $product
     * @return float
     */
    public function getProductAmount(?ProductInterface $product): float
    {
        $productId = $this->request->getParam('id');
        if ($product) {
            return $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        }
        if ($productId) {
            $product = $this->productRepository->getById($productId);
            return $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        }
        return 0;
    }

    /**
     * @param $productId
     * @return float
     */
    public function getDiscountAmount($productId): float
    {
        $product = $this->productRepository->getById($productId);
        $discountAmount = 0;
        try {
            $discountAmount = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue() - $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        } catch (NoSuchEntityException $e) {
            return 0;
        }
        return $discountAmount;
    }

}
