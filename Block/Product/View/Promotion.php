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
            && $this->config->isExpressCheckoutActive()
            && $this->config->isBankInstallmentsActive();
    }

    /**
     * Get product price
     *
     * @return float
     */
    public function getPrice(): float
    {
        $quote = $this->checkoutSession->getQuote();
        return $quote ? (float) $quote->getGrandTotal() : 0.0;
    }

    /**
     * Get installment count
     *
     * @return int
     */
    public function getInstallmentsCount(): int
    {
        return 6;
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
}
