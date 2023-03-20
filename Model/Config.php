<?php
declare(strict_types=1);

namespace Amwal\Payments\Model;

use Amwal\Payments\Model\Config\Checkout\ConfigProvider;
use Amwal\Payments\Model\Config\Source\MerchantMode;
use Magento\Config\Model\Config\Backend\Admin\Custom as AdminConfig;
use Magento\Directory\Model\Currency;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Composer\ComposerInformation;
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
    public const XML_CONFIG_PATH_MERCHANT_MODE = 'payment/amwal_payments/merchant_mode';
    public const XML_CONFIG_PATH_COUNTRY_CODE = 'payment/amwal_payments/country_code';
    public const XML_CONFIG_PATH_TITLE = 'payment/amwal_payments/title';
    public const XML_CONFIG_PATH_EXPRESS_CHECKOUT_TITLE = 'payment/amwal_payments/express_checkout_title';
    public const XML_CONFIG_PATH_ORDER_CONFIRMED_STATUS = 'payment/amwal_payments/order_confirmed_status';
    public const XML_CONFIG_PATH_CREATE_USER_ON_ORDER = 'payment/amwal_payments/create_user_on_order';
    public const XML_CONFIG_PATH_DARK_MODE = 'payment/amwal_payments/dark_mode';
    public const XML_CONFIG_PATH_PHONE_NUMBER_FORMAT = 'payment/amwal_payments/phone_number_format';
    public const XML_CONFIG_PATH_PHONE_NUMBER_FORMAT_COUNTRY = 'payment/amwal_payments/phone_number_format_country';
    public const XML_CONFIG_PATH_PHONE_NUMBER_TRIM_WHITESPACE = 'payment/amwal_payments/phone_number_trim_whitespace';
    public const XML_CONFIG_PATH_DEBUG_MODE = 'payment/amwal_payments/debug_mode';
    public const XML_CONFIG_PATH_ALLOW_SPECIFIC = 'payment/amwal_payments/allowspecific';
    public const XML_CONFIG_PATH_SPECIFIC_COUNTRIES = 'payment/amwal_payments/specificcountries';
    public const XML_CONFIG_PATH_LIMIT_REGIONS = 'payment/amwal_payments/limit_regions';
    public const XML_CONFIG_PATH_TEST_API_BASE_URL = 'payment/amwal_payments/test_api_base_url';
    public const XML_CONFIG_PATH_PROD_API_BASE_URL = 'payment/amwal_payments/prod_api_base_url';

    /** @var ScopeConfigInterface  */
    private ScopeConfigInterface $scopeConfig;

    /** @var ComposerInformation  */
    private ComposerInformation $composerInformation;

    /** @var RegionCollectionFactory  */
    private RegionCollectionFactory $regionCollectionFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ComposerInformation $composerInformation
     * @param RegionCollectionFactory $regionCollectionFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ComposerInformation $composerInformation,
        RegionCollectionFactory $regionCollectionFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->composerInformation = $composerInformation;
        $this->regionCollectionFactory = $regionCollectionFactory;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_ACTIVE, ScopeInterface::SCOPE_WEBSITE) &&
            $this->getMerchantId() &&
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
    public function shouldCreateCustomer(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_CREATE_USER_ON_ORDER, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return bool
     */
    public function isDarkModeEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_DARK_MODE, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function getPhoneNumberFormat(): string
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_PATH_PHONE_NUMBER_FORMAT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string|null
     */
    public function getPhoneNumberFormatCountry(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_PATH_PHONE_NUMBER_FORMAT_COUNTRY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function getPhoneNumberTrimWhitespace(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_PHONE_NUMBER_TRIM_WHITESPACE, ScopeInterface::SCOPE_STORE);
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
        return $this->scopeConfig->getValue(self::XML_CONFIG_PATH_SPECIFIC_COUNTRIES, ScopeInterface::SCOPE_WEBSITE) ?? [];
    }

    /**
     * @return array
     */
    public function getLimitedRegions(): array
    {
        $regionIds = $this->scopeConfig->getValue(self::XML_CONFIG_PATH_LIMIT_REGIONS, ScopeInterface::SCOPE_WEBSITE) ?? '';
        return explode(',', $regionIds);
    }

    /**
     * @return array
     */
    public function getLimitedRegionsArray(): array
    {
        $limitedRegionCodes = [];
        $limitedRegions = $this->getLimitedRegions();
        $regionCollection = $this->regionCollectionFactory->create();
        $regionCollection->addFieldToFilter('main_table.region_id', ['in' => $limitedRegions]);
        foreach ($regionCollection->getItems() as $region) {
            $limitedRegionCodes[$region->getCountryId()][$region->getRegionId()] = $region->getName();
        }

        return $limitedRegionCodes;
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

    /**
     * @return string
     */
    public function getVersion(): string
    {
        $packages = $this->composerInformation->getInstalledMagentoPackages();
        return $packages['amwal/payments']['version'] ?? 'unknown';
    }
}
