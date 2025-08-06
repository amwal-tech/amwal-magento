<?php
declare(strict_types=1);

namespace Amwal\Payments\Block\Product\View;

use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Block\Product\Context as ProductContext;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\Json\EncoderInterface as JsonEncoderInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\Config\Source\ModuleType;
use Amwal\Payments\Model\CurrencyConverter;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD)
 */
class Promotion extends View
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * Currency converter instance
     *
     * @var CurrencyConverter
     */

    /**
     * @var bool
     */
    protected $isOnShoppingCartPage = false;

    /**
     * @param ProductContext $context
     * @param EncoderInterface $urlEncoder
     * @param JsonEncoderInterface $jsonEncoder
     * @param StringUtils $string
     * @param Product $productHelper
     * @param ConfigInterface $productTypeConfig
     * @param FormatInterface $localeFormat
     * @param CustomerSession $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param Config $config
     * @param CheckoutSession $checkoutSession
     * @param CurrencyConverter $currencyConverter
     * @param array $data
     */
    public function __construct(
        ProductContext $context,
        EncoderInterface $urlEncoder,
        JsonEncoderInterface $jsonEncoder,
        StringUtils $string,
        Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        CustomerSession $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        Config $config,
        CheckoutSession $checkoutSession,
        CurrencyConverter $currencyConverter,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * Set flag to indicate this is on shopping cart page
     *
     * @return $this
     */
    public function setIsOnShoppingCartPage(): self
    {
        $this->isOnShoppingCartPage = true;
        return $this;
    }

    /**
     * Check if this is on shopping cart page
     *
     * @return bool
     */
    public function isOnShoppingCartPage(): bool
    {
        return $this->isOnShoppingCartPage;
    }

    /**
     * Check if promotions are active
     *
     * @return bool
     */
    public function isPromotionsActive(): bool
    {
        if ($this->config->getModuleType() === ModuleType::MODULE_TYPE_LITE) {
            return false;
        }

        return $this->config->isActive()
            && $this->config->isExpressCheckoutActive();
    }

    /**
     * Get product price
     *
     * @return float
     */
    public function getPrice(): float
    {
        try {
            // If on cart page, get cart total instead of product price
            if ($this->isOnShoppingCartPage()) {
                return $this->getCartTotal();
            }

            $product = $this->getProduct();

            if (!$product || !$product->getId()) {
                return 0.0;
            }

            // Get the quote object for currency conversion
            $quote = $this->checkoutSession->getQuote();

            // Get the final price including all discounts and taxes
            $finalPrice = $product->getPriceInfo()->getPrice('final_price');
            return $this->currencyConverter->convertToSAR($finalPrice->getAmount()->getValue(), $quote);

        } catch (\Exception $e) {
            // Log error if needed
            $this->_logger->error('Amwal Promotion: Error getting product price: ' . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Get cart total for shopping cart page
     *
     * @return float
     */
    protected function getCartTotal(): float
    {
        try {
            $quote = $this->checkoutSession->getQuote();
            
            if (!$quote || !$quote->getId()) {
                return 0.0;
            }

            // Get grand total including taxes, shipping, discounts
            $grandTotal = $quote->getGrandTotal();
            
            return (float) $grandTotal;
        } catch (\Exception $e) {
            $this->_logger->error('Amwal Promotion: Error getting cart total: ' . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Get installment count
     *
     * @return int
     */
    public function getInstallmentsCount(): int
    {
        return 12;
    }

    /**
     * Get promotion URL
     *
     * @return string
     */
    public function getPromotionUrl(): string
    {
        return 'https://pay.sa.amwal.tech/installment-promotion';
    }

    /**
     * Get formatted price for display
     *
     * @return string
     */
    public function getFormattedPrice(): string
    {
        $price = $this->getPrice();
        return $this->priceCurrency->format($price, false);
    }

    /**
     * Get installment price
     *
     * @return float
     */
    public function getInstallmentPrice(): float
    {
        $totalPrice = $this->getPrice();
        $installmentsCount = $this->getInstallmentsCount();

        if ($installmentsCount <= 0) {
            return $totalPrice;
        }

        return $totalPrice / $installmentsCount;
    }

    /**
     * Get formatted installment price
     *
     * @return string
     */
    public function getFormattedInstallmentPrice(): string
    {
        $installmentPrice = $this->getInstallmentPrice();
        return $this->priceCurrency->format($installmentPrice, false);
    }
}
