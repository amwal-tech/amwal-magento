<?php
declare(strict_types=1);

namespace Amwal\Payments\Api\Data;

interface AmwalButtonConfigInterface
{
    public const ID_PREFIX = 'amwal-checkout-';
    public const MERCHANT_ID = 'merchant_id';
    public const AMOUNT = 'amount';
    public const COUNTRY_CODE = 'country_code';
    public const LOCALE = 'locale';
    public const DARK_MODE = 'dark_mode';
    public const EMAIL_REQUIRED = 'email_required';
    public const ADDRESS_REQUIRED = 'address_required';
    public const ADDRESS_HANDSHAKE = 'address_handshake';
    public const REF_ID = 'ref_id';
    public const LABEL = 'label';
    public const DISABLED = 'disabled';
    public const SHOW_PAYMENT_BRANDS = 'show_payment_brands';
    public const ENABLE_PRE_CHECKOUT_TRIGGER = 'enable_pre_checkout_trigger';
    public const ENABLE_PRE_PAY_TRIGGER = 'enable_pre_pay_trigger';
    public const ID = 'id';
    public const TEST_ENVIRONMENT = 'test_environment';
    public const ALLOWED_ADDRESS_COUNTRIES = 'allowed_address_countries';
    public const ALLOWED_ADDRESS_STATES = 'allowed_address_states';
    public const ALLOWED_ADDRESS_CITIES = 'allowed_address_cities';
    public const INITIAL_ADDRESS = 'initial_address';
    public const INITIAL_EMAIL = 'initial_email';
    public const INITIAL_PHONE = 'initial_phone';
    public const PLUGIN_VERSION = 'plugin_version';
    public const QUOTE_ID = 'quote_id';

    /**
     * @return string
     */
    public function getMerchantId(): string;

    /**
     * @param string $merchantId
     * @return AmwalButtonConfigInterface
     */
    public function setMerchantId(string $merchantId): AmwalButtonConfigInterface;

    /**
     * @return float
     */
    public function getAmount(): float;

    /**
     * @param float $amount
     * @return AmwalButtonConfigInterface
     */
    public function setAmount(float $amount): AmwalButtonConfigInterface;

    /**
     * @return string
     */
    public function getCountryCode(): string;

    /**
     * @param string $countryCode
     * @return AmwalButtonConfigInterface
     */
    public function setCountryCode(string $countryCode): AmwalButtonConfigInterface;

    /**
     * @return string
     */
    public function getLocale(): string;

    /**
     * @param string $locale
     * @return AmwalButtonConfigInterface
     */
    public function setLocale(string $locale): AmwalButtonConfigInterface;

    /**
     * @return string
     */
    public function getDarkMode(): string;

    /**
     * @param string $darkMode
     * @return AmwalButtonConfigInterface
     */
    public function setDarkMode(string $darkMode): AmwalButtonConfigInterface;

    /**
     * @return bool
     */
    public function getEmailRequired(): bool;

    /**
     * @param bool $emailRequired
     * @return AmwalButtonConfigInterface
     */
    public function setEmailRequired(bool $emailRequired): AmwalButtonConfigInterface;

    /**
     * @return bool
     */
    public function getAddressRequired(): bool;

    /**
     * @param bool $addressRequired
     * @return AmwalButtonConfigInterface
     */
    public function setAddressRequired(bool $addressRequired): AmwalButtonConfigInterface;

    /**
     * @return bool
     */
    public function getAddressHandshake(): bool;

    /**
     * @param bool $addressHandshake
     * @return AmwalButtonConfigInterface
     */
    public function setAddressHandshake(bool $addressHandshake): AmwalButtonConfigInterface;

    /**
     * @return string
     */
    public function getRefId(): string;

    /**
     * @param string $refId
     * @return AmwalButtonConfigInterface
     */
    public function setRefId(string $refId): AmwalButtonConfigInterface;

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @param string $label
     * @return AmwalButtonConfigInterface
     */
    public function setLabel(string $label): AmwalButtonConfigInterface;

    /**
     * @return bool
     */
    public function getDisabled(): bool;

    /**
     * @param bool $disabled
     * @return AmwalButtonConfigInterface
     */
    public function setDisabled(bool $disabled): AmwalButtonConfigInterface;

    /**
     * @return bool
     */
    public function getShowPaymentBrands(): bool;

    /**
     * @param bool $showPaymentBrands
     * @return AmwalButtonConfigInterface
     */
    public function setShowPaymentBrands(bool $showPaymentBrands): AmwalButtonConfigInterface;

    /**
     * @return bool
     */
    public function getEnablePreCheckoutTrigger(): bool;

    /**
     * @param bool $enablePreCheckoutTrigger
     * @return AmwalButtonConfigInterface
     */
    public function setEnablePreCheckoutTrigger(bool $enablePreCheckoutTrigger): AmwalButtonConfigInterface;

    /**
     * @return bool
     */
    public function getEnablePrePayTrigger(): bool;

    /**
     * @param bool $enablePrePayTrigger
     * @return AmwalButtonConfigInterface
     */
    public function setEnablePrePayTrigger(bool $enablePrePayTrigger): AmwalButtonConfigInterface;

    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @param string $id
     * @return AmwalButtonConfigInterface
     */
    public function setId(string $id): AmwalButtonConfigInterface;

    /**
     * @return string|null
     */
    public function getTestEnvironment(): ?string;

    /**
     * @param string|null $testEnvironment
     * @return AmwalButtonConfigInterface
     */
    public function setTestEnvironment(?string $testEnvironment): AmwalButtonConfigInterface;

    /**
     * @return array|null
     */
    public function getAllowedAddressCountries(): ?array;

    /**
     * @param array|null $allowedAddressCities
     * @return AmwalButtonConfigInterface
     */
    public function setAllowedAddressCountries(?array $allowedAddressCountries): AmwalButtonConfigInterface;

    /**
     * @return array|null
     */
    public function getAllowedAddressStates(): ?string;

    /**
     * @param array|null $allowedAddressStates
     * @return AmwalButtonConfigInterface
     */
    public function setAllowedAddressStates(?string $allowedAddressStates): AmwalButtonConfigInterface;

    /**
     * @return array|null
     */
    public function getAllowedAddressCities(): ?string;

    /**
     * @param array|null $allowedAddressCities
     * @return AmwalButtonConfigInterface
     */
    public function setAllowedAddressCities(?string $allowedAddressCities): AmwalButtonConfigInterface;

    /**
     * @return string|null
     */
    public function getInitialAddress(): ?string;

    /**
     * @param string|null $initialAddress
     * @return AmwalButtonConfigInterface
     */
    public function setInitialAddress(?string $initialAddress): AmwalButtonConfigInterface;

    /**
     * @return string|null
     */
    public function getInitialEmail(): ?string;

    /**
     * @param string|null $initialEmail
     * @return AmwalButtonConfigInterface
     */
    public function setInitialEmail(?string $initialEmail): AmwalButtonConfigInterface;

    /**
     * @return string|null
     */
    public function getInitialPhone(): ?string;

    /**
     * @param string|null $initialPhone
     * @return AmwalButtonConfigInterface
     */
    public function setInitialPhone(?string $initialPhone): AmwalButtonConfigInterface;

    /**
     * @return string|null
     */
    public function getPluginVersion(): ?string;

    /**
     * @param string|null $initialPhone
     * @return AmwalButtonConfigInterface
     */
    public function setPluginVersion(?string $pluginVersion): AmwalButtonConfigInterface;

    /**
     * @return string|null
     */
    public function getQuoteId(): ?string;

    /**
     * @param string|null $quoteId
     * @return AmwalButtonConfigInterface
     */
    public function setQuoteId(?string $quoteId): AmwalButtonConfigInterface;
}
