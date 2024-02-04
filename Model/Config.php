<?php
declare(strict_types=1);

namespace Amwal\Payments\Model;

use Amwal\Payments\Model\Config\Checkout\ConfigProvider;
use Amwal\Payments\Model\Config\Source\MerchantMode;
use Magento\Config\Model\Config\Backend\Admin\Custom as AdminConfig;
use Magento\Directory\Model\Currency;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Config\Config as GatewayConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Directory\Helper\Data as DirectoryHelper;

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
    public const XML_CONFIG_PATH_USE_BASE_CURRENCY = 'payment/amwal_payments/use_base_currency';
    public const XML_CONFIG_PATH_ALLOW_SPECIFIC = 'payment/amwal_payments/allowspecific';
    public const XML_CONFIG_PATH_SPECIFIC_COUNTRIES = 'payment/amwal_payments/specificcountries';
    public const XML_CONFIG_PATH_LIMIT_REGIONS = 'payment/amwal_payments/limit_regions';
    public const XML_CONFIG_PATH_TEST_API_BASE_URL = 'payment/amwal_payments/test_api_base_url';
    public const XML_CONFIG_PATH_PROD_API_BASE_URL = 'payment/amwal_payments/prod_api_base_url';
    public const XML_CONFIG_PATH_STREET_LINE_COUNT = 'customer/address/street_lines';
    public const XML_CONFIG_PATH_SECRET_KEY = 'payment/amwal_payments/secret_key';
    public const XML_CONFIG_PATH_INSTALLMENT_CALLBACK = 'payment/amwal_payments/installment_callback';
    public const XML_CONFIG_PATH_USE_SYSTEM_COUNTRY_SETTINGS = 'payment/amwal_payments/use_system_country_settings';
    public const XML_CONFIG_PATH_STYLE_CSS = 'payment/amwal_payments/style_css';
    public const XML_CONFIG_PATH_SENTRY_REPORT = 'payment/amwal_payments/sentry_report';
    public const XML_CONFIG_PATH_CRONJOB_ENABLE = 'payment/amwal_payments/cronjob_enable';
    public const XML_CONFIG_PATH_ORDER_STATUS_CHANGED_CUSTOMER_EMAIL = 'payment/amwal_payments/order_status_changed_customer_email';
    public const XML_CONFIG_PATH_ORDER_STATUS_CHANGED_ADMIN_EMAIL = 'payment/amwal_payments/order_status_changed_admin_email';
    public const XML_CONFIG_PATH_DISCOUNT_RIBBON = 'payment/amwal_payments/show_discount_ribbon';
    public const XML_CONFIG_PATH_ENABLE_PRE_CHECKOUT_TRIGGER = 'payment/amwal_payments/enable_pre_checkout_trigger';

    /**
     * @var string
     */
    const MODULE_VERSION = '1.0.32';

    /** @var ScopeConfigInterface */
    private ScopeConfigInterface $scopeConfig;

    /** @var RegionCollectionFactory */
    private RegionCollectionFactory $regionCollectionFactory;

    /** @var DirectoryHelper */
    private DirectoryHelper $directoryHelper;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param DirectoryHelper $directoryHelper
     */
    public function __construct(
        ScopeConfigInterface    $scopeConfig,
        RegionCollectionFactory $regionCollectionFactory,
        DirectoryHelper         $directoryHelper
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->directoryHelper = $directoryHelper;
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
        return (string)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_MERCHANT_ID, ScopeInterface::SCOPE_WEBSITE);
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
        // TODO: Currently, it returns a static value ('SA'), which might not reflect the actual system or user settings.
        return 'SA';
        /*
        if ($this->shouldUseSystemCountrySettings()) {
            return $this->scopeConfig->getValue(
                'general/country/default',
                ScopeInterface::SCOPE_STORE
            );
        }
        return (string)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_COUNTRY_CODE, ScopeInterface::SCOPE_WEBSITE);
        */
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_TITLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getExpressCheckoutTitle(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_EXPRESS_CHECKOUT_TITLE, ScopeInterface::SCOPE_STORE);
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
     * @return bool
     */
    public function shouldUseBaseCurrency(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_USE_BASE_CURRENCY);
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
    public function getAllowCountries(): array
    {
        return explode(',', $this->scopeConfig->getValue('general/country/allow', ScopeInterface::SCOPE_STORE)) ?? [];
    }


    /**
     * @return array
     */
    public function getLimitedRegions(): array
    {
        if ($this->shouldUseSystemCountrySettings()) {
            $countryCodes = $this->getAllowCountries();
            $regionCollection = $this->regionCollectionFactory->create();
            $regionCollection->addFieldToFilter('main_table.country_id', ['in' => $countryCodes]);
            return $regionCollection->getColumnValues('region_id');
        }
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

    public function getAllowedAddressCountries(): array
    {
        if ($this->shouldUseSystemCountrySettings()) {
            return explode(',', $this->scopeConfig->getValue(
                'general/country/allow',
                ScopeInterface::SCOPE_STORE
            ));
        }
        return array_keys($this->directoryHelper->getCountryCollection()->getItems());
    }

    /**
     * @return string
     */
    public function getApiBaseUrl(): string
    {
        if ($this->getMerchantMode() === MerchantMode::MERCHANT_TEST_MODE) {
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
     * @return bool
     */
    public function shouldCombineStreetLines(): bool
    {
        $lineCount = (int)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_STREET_LINE_COUNT, ScopeInterface::SCOPE_STORE);
        return $lineCount < 2;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return self::MODULE_VERSION;
    }

    /**
     * @return array
     */
    public function getPostcodeOptionalCountries(): array
    {
        $optionalCountries = [];
        $getOptionalCountries = $this->scopeConfig->getValue(
            'general/country/optional_zip_countries',
            ScopeInterface::SCOPE_STORE
        );
        if ($getOptionalCountries) {
            $optionalCountries = explode(',', $getOptionalCountries);
        }
        return $optionalCountries;
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_SECRET_KEY, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function getInstallmentOptionsUrl(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_INSTALLMENT_CALLBACK, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function getStyleCss(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_STYLE_CSS, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return bool
     */
    public function shouldUseSystemCountrySettings(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_USE_SYSTEM_COUNTRY_SETTINGS, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return bool
     */
    public function isSentryReportEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_SENTRY_REPORT, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return bool
     */
    public function isCronJobEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_CRONJOB_ENABLE, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return bool
     */
    public function isOrderStatusChangedCustomerEmailEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_ORDER_STATUS_CHANGED_CUSTOMER_EMAIL, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return bool
     */
    public function isOrderStatusChangedAdminEmailEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_ORDER_STATUS_CHANGED_ADMIN_EMAIL, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return bool
     */
    public function isDiscountRibbonEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_DISCOUNT_RIBBON, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return bool
     */
    public function isPreCheckoutTriggerEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_ENABLE_PRE_CHECKOUT_TRIGGER, ScopeInterface::SCOPE_WEBSITE);
    }

}
