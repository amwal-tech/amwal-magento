<?php
declare(strict_types=1);

namespace Amwal\Payments\Model;

use Amwal\Payments\Model\Config\Checkout\ConfigProvider;
use Amwal\Payments\Model\Config\Source\MerchantMode;
use Magento\Config\Model\Config\Backend\Admin\Custom as AdminConfig;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Config\Config as GatewayConfig;
use Magento\Store\Model\ScopeInterface;

class Config
{

    public const XML_CONFIG_PATH_ACTIVE = 'payment/amwal_payments/active';
    public const XML_CONFIG_PATH_MERCHANT_ID_VALID = 'payment/amwal_payments/merchant_id_valid';
    public const XML_CONFIG_PATH_EXPRESS_CHECKOUT_ACTIVE = 'payment/amwal_payments/express_checkout_active';
    public const XML_CONFIG_PATH_REGULAR_CHECKOUT_ACTIVE = 'payment/amwal_payments/regular_checkout_active';
    public const XML_CONFIG_PATH_HIDE_PROCEED_TO_CHECKOUT = 'payment/amwal_payments/hide_proceed_to_checkout';
    public const XML_CONFIG_PATH_MERCHANT_ID = 'payment/amwal_payments/merchant_id';
    public const XML_CONFIG_PATH_REF_ID_SECRET = 'payment/amwal_payments/ref_id_secret';
    public const XML_CONFIG_PATH_MERCHANT_MODE = 'payment/amwal_payments/merchant_mode';
    public const XML_CONFIG_PATH_COUNTRY_CODE = 'payment/amwal_payments/country_code';
    public const XML_CONFIG_PATH_TITLE = 'payment/amwal_payments/title';
    public const XML_CONFIG_PATH_EXPRESS_CHECKOUT_TITLE =  'payment/amwal_payments/express_checkout_title';
    public const XML_CONFIG_PATH_ORDER_CONFIRMED_STATUS =  'payment/amwal_payments/order_confirmed_status';
    public const XML_CONFIG_PATH_DARK_MODE = 'payment/amwal_payments/dark_mode';
    public const XML_CONFIG_PATH_DEBUG_MODE = 'payment/amwal_payments/debug_mode';
    public const XML_CONFIG_PATH_CURRENCY = 'payment/amwal_payments/currency';
    public const XML_CONFIG_PATH_ALLOW_SPECIFIC = 'payment/amwal_payments/allowspecific';
    public const XML_CONFIG_PATH_SPECIFIC_COUNTRIES = 'payment/amwal_payments/specificcountries';
    public const XML_CONFIG_PATH_TEST_API_BASE_URL = 'payment/amwal_payments/test_api_base_url';
    public const XML_CONFIG_PATH_PROD_API_BASE_URL = 'payment/amwal_payments/prod_api_base_url';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_ACTIVE, ScopeInterface::SCOPE_WEBSITE) &&
            $this->getMerchantId() &&
            $this->getRefIdSecret() &&
            $this->isMerchantValid();
    }

    /**
     * @return bool
     */
    public function isMerchantValid(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_MERCHANT_ID_VALID, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return bool
     */
    public function isExpressCheckoutActive(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_EXPRESS_CHECKOUT_ACTIVE, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return bool
     */
    public function isRegularCheckoutActive(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_REGULAR_CHECKOUT_ACTIVE, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return bool
     */
    public function shouldHideProceedToCheckout(): bool
    {
        return $this->isExpressCheckoutActive() && $this->scopeConfig->isSetFlag(
            self::XML_CONFIG_PATH_HIDE_PROCEED_TO_CHECKOUT,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_CONFIG_PATH_MERCHANT_ID, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function getRefIdSecret(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_CONFIG_PATH_REF_ID_SECRET, ScopeInterface::SCOPE_WEBSITE);
    }


    /**
     * @return string
     */
    public function getMerchantMode(): string
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_PATH_MERCHANT_MODE, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_CONFIG_PATH_COUNTRY_CODE, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_CONFIG_PATH_TITLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getExpressCheckoutTitle(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_CONFIG_PATH_EXPRESS_CHECKOUT_TITLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getOrderConfirmedStatus(): string
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_PATH_ORDER_CONFIRMED_STATUS, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return bool
     */
    public function isDarkModeEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_DARK_MODE, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return bool
     */
    public function isDebugModeEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_DEBUG_MODE);
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->scopeConfig->getValue(Currency::XML_PATH_CURRENCY_BASE, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return bool
     */
    public function isAllowSpecific(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_ALLOW_SPECIFIC, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return array
     */
    public function getSpecificCountries(): array
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_PATH_SPECIFIC_COUNTRIES, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function getApiBaseUrl(): string
    {
        if ( $this->getMerchantMode() === MerchantMode::MERCHANT_TEST_MODE) {
            return $this->scopeConfig->getValue(self::XML_CONFIG_PATH_TEST_API_BASE_URL);
        }

        return $this->scopeConfig->getValue(self::XML_CONFIG_PATH_PROD_API_BASE_URL);
    }

    /**
     * @param string $field
     * @param int|null $storeId
     * @return mixed
     */
    public function getPaymentConfig(string $field, ?int $storeId = null): mixed
    {
        $path = sprintf(GatewayConfig::DEFAULT_PATH_PATTERN, ConfigProvider::CODE, $field);
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        $storeLocale = $this->scopeConfig->getValue(AdminConfig::XML_PATH_GENERAL_LOCALE_CODE, ScopeInterface::SCOPE_STORE);
        return substr($storeLocale, 0, 2);
    }
}
