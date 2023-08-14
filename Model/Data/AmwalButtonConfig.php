<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Data;

use Amwal\Payments\Api\Data\AmwalButtonConfigInterface;
use Magento\Framework\DataObject;

class AmwalButtonConfig extends DataObject implements AmwalButtonConfigInterface
{

    /**
     * @inheritDoc
     */
    public function getMerchantId(): string
    {
        return $this->getData(self::MERCHANT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setMerchantId(string $merchantId): AmwalButtonConfigInterface
    {
        return $this->setData(self::MERCHANT_ID, $merchantId);
    }

    /**
     * @inheritDoc
     */
    public function getAmount(): float
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setAmount(float $amount): AmwalButtonConfigInterface
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * @inheritDoc
     */
    public function getCountryCode(): string
    {
        return $this->getData(self::COUNTRY_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setCountryCode(string $countryCode): AmwalButtonConfigInterface
    {
        return $this->setData(self::COUNTRY_CODE, $countryCode);
    }

    /**
     * @inheritDoc
     */
    public function getLocale(): string
    {
        return $this->getData(self::LOCALE);
    }

    /**
     * @inheritDoc
     */
    public function setLocale(string $locale): AmwalButtonConfigInterface
    {
        return $this->setData(self::LOCALE, $locale);
    }

    /**
     * @inheritDoc
     */
    public function getDarkMode(): string
    {
        return $this->getData(self::DARK_MODE);
    }

    /**
     * @inheritDoc
     */
    public function setDarkMode(string $darkMode): AmwalButtonConfigInterface
    {
        return $this->setData(self::DARK_MODE, $darkMode);
    }

    /**
     * @inheritDoc
     */
    public function getEmailRequired(): bool
    {
        return $this->getData(self::EMAIL_REQUIRED);
    }

    /**
     * @inheritDoc
     */
    public function setEmailRequired(bool $emailRequired): AmwalButtonConfigInterface
    {
        return $this->setData(self::EMAIL_REQUIRED, $emailRequired);
    }

    /**
     * @inheritDoc
     */
    public function getAddressRequired(): bool
    {
        return $this->getData(self::ADDRESS_REQUIRED);
    }

    /**
     * @inheritDoc
     */
    public function setAddressRequired(bool $addressRequired): AmwalButtonConfigInterface
    {
        return $this->setData(self::ADDRESS_REQUIRED, $addressRequired);
    }

    /**
     * @inheritDoc
     */
    public function getAddressHandshake(): bool
    {
        return $this->getData(self::ADDRESS_HANDSHAKE);
    }

    /**
     * @inheritDoc
     */
    public function setAddressHandshake(bool $addressHandshake): AmwalButtonConfigInterface
    {
        return $this->setData(self::ADDRESS_HANDSHAKE, $addressHandshake);
    }

    /**
     * @inheritDoc
     */
    public function getRefId(): string
    {
        return $this->getData(self::REF_ID);
    }

    /**
     * @inheritDoc
     */
    public function setRefId(string $refId): AmwalButtonConfigInterface
    {
        return $this->setData(self::REF_ID, $refId);
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return $this->getData(self::LABEL);
    }

    /**
     * @inheritDoc
     */
    public function setLabel(string $label): AmwalButtonConfigInterface
    {
        return $this->setData(self::LABEL, $label);
    }

    /**
     * @inheritDoc
     */
    public function getDisabled(): bool
    {
        return $this->getData(self::DISABLED);
    }

    /**
     * @inheritDoc
     */
    public function setDisabled(bool $disabled): AmwalButtonConfigInterface
    {
        return $this->setData(self::DISABLED, $disabled);
    }

    /**
     * @inheritDoc
     */
    public function getShowPaymentBrands(): bool
    {
        return $this->getData(self::SHOW_PAYMENT_BRANDS);
    }

    /**
     * @inheritDoc
     */
    public function setShowPaymentBrands(bool $showPaymentBrands): AmwalButtonConfigInterface
    {
        return $this->setData(self::SHOW_PAYMENT_BRANDS, $showPaymentBrands);
    }

    /**
     * @inheritDoc
     */
    public function getEnablePreCheckoutTrigger(): bool
    {
        return $this->getData(self::ENABLE_PRE_CHECKOUT_TRIGGER);
    }

    /**
     * @inheritDoc
     */
    public function setEnablePreCheckoutTrigger(bool $enablePreCheckoutTrigger): AmwalButtonConfigInterface
    {
        return $this->setData(self::ENABLE_PRE_CHECKOUT_TRIGGER, $enablePreCheckoutTrigger);
    }

    /**
     * @inheritDoc
     */
    public function getEnablePrePayTrigger(): bool
    {
        return $this->getData(self::ENABLE_PRE_PAY_TRIGGER);
    }

    /**
     * @inheritDoc
     */
    public function setEnablePrePayTrigger(bool $enablePrePayTrigger): AmwalButtonConfigInterface
    {
        return $this->setData(self::ENABLE_PRE_PAY_TRIGGER, $enablePrePayTrigger);
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function setId(string $id): AmwalButtonConfigInterface
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getTestEnvironment(): ?string
    {
        return $this->getData(self::TEST_ENVIRONMENT);
    }

    /**
     * @inheritDoc
     */
    public function setTestEnvironment(?string $testEnvironment): AmwalButtonConfigInterface
    {
        return $this->setData(self::TEST_ENVIRONMENT, $testEnvironment);
    }

    /**
     * @inheritDoc
     */
    public function getAllowedAddressCountries(): ?array
    {
        return $this->getData(self::ALLOWED_ADDRESS_COUNTRIES);
    }

    /**
     * @inheritDoc
     */
    public function setAllowedAddressCountries(?array $allowedAddressCountries): AmwalButtonConfigInterface
    {
        return $this->setData(self::ALLOWED_ADDRESS_COUNTRIES, $allowedAddressCountries);
    }

    /**
     * @inheritDoc
     */
    public function getAllowedAddressStates(): ?string
    {
        return $this->getData(self::ALLOWED_ADDRESS_STATES);
    }

    /**
     * @inheritDoc
     */
    public function setAllowedAddressStates(?string $allowedAddressStates): AmwalButtonConfigInterface
    {
        return $this->setData(self::ALLOWED_ADDRESS_STATES, $allowedAddressStates);
    }

    /**
     * @inheritDoc
     */
    public function getAllowedAddressCities(): ?string
    {
        return $this->getData(self::ALLOWED_ADDRESS_CITIES);
    }

    /**
     * @inheritDoc
     */
    public function setAllowedAddressCities(?string $allowedAddressCities): AmwalButtonConfigInterface
    {
        return $this->setData(self::ALLOWED_ADDRESS_CITIES, $allowedAddressCities);
    }

    /**
     * @inheritDoc
     */
    public function getInitialAddress(): ?string
    {
        return $this->getData(self::INITIAL_ADDRESS);
    }

    /**
     * @inheritDoc
     */
    public function setInitialAddress(?string $initialAddress): AmwalButtonConfigInterface
    {
        return $this->setData(self::INITIAL_ADDRESS, $initialAddress);
    }

    /**
     * @inheritDoc
     */
    public function getInitialEmail(): ?string
    {
        return $this->getData(self::INITIAL_EMAIL);
    }

    /**
     * @inheritDoc
     */
    public function setInitialEmail(?string $initialEmail): AmwalButtonConfigInterface
    {
        return $this->setData(self::INITIAL_EMAIL, $initialEmail);
    }

    /**
     * @inheritDoc
     */
    public function getInitialPhone(): ?string
    {
        return $this->getData(self::INITIAL_PHONE);
    }

    /**
     * @inheritDoc
     */
    public function setInitialPhone(?string $initialPhone): AmwalButtonConfigInterface
    {
        return $this->setData(self::INITIAL_PHONE, $initialPhone);
    }

    /**
     * @inheritDoc
     */
    public function getPluginVersion(): ?string
    {
        return $this->getData(self::PLUGIN_VERSION);
    }

    /**
     * @inheritDoc
     */
    public function setPluginVersion(?string $pluginVersion): AmwalButtonConfigInterface
    {
        return $this->setData(self::PLUGIN_VERSION, $pluginVersion);
    }

    /**
     * @inheritDoc
     */
    public function getQuoteId(): ?string
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setQuoteId(?string $quoteId): AmwalButtonConfigInterface
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }



    /**
     * @inheritDoc
     */
    public function getPostCodeOptionalCountries(): ?array
    {
        return $this->getData(self::POSTCODE_OPTIONAL_COUNTRIES);
    }

    /**
     * @inheritDoc
     */
    public function setPostCodeOptionalCountries(?array $postCodeOptionalCountries): AmwalButtonConfigInterface
    {
        return $this->setData(self::POSTCODE_OPTIONAL_COUNTRIES, $postCodeOptionalCountries);
    }
}
