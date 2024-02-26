<?php
declare(strict_types=1);

namespace Amwal\Payments\Test\Unit\Api\Data;

use Amwal\Payments\Api\Data\AmwalButtonConfigInterface;
use PHPUnit\Framework\TestCase;

class AmwalButtonConfigInterfaceTest extends TestCase
{
    private const AMOUNT = 100.00;
    private const INSTALLMENT_OPTIONS_URL = 'https://store.amwal.tech/checkout';
    private const MERCHANT_ID = 'sandbox-amwal-e09ee380-d8c7-4710-a6ab-c9b39c7ffd47';
    private const TEST_ENVIRONMENT = 'qa';
    private const ENABLE_PRE_CHECKOUT_TRIGGER = true;
    private const ENABLE_PRE_PAY_TRIGGER = true;
    private const SHOW_PAYMENT_BRANDS = true;
    private const DISABLED = false;
    private const LABEL = 'Pay with Amwal';
    private const REF_ID = 'd203015667ee4ac7d81fb564edd8cc1a37cce05f8ab6aad09d8057427daaa972';
    private const ADDRESS_HANDSHAKE = true;
    private const ADDRESS_REQUIRED = true;
    private const EMAIL_REQUIRED = true;
    private const DARK_MODE = 'on';
    private const COUNTRY_CODE = 'SA';
    private const ALLOWED_ADDRESS_COUNTRIES = ['SA'];
    private const POSTCODE_OPTIONAL_COUNTRIES = ['SA'];
    private const ALLOWED_ADDRESS_STATES = ['SA'];
    private const ALLOWED_ADDRESS_CITIES = ['SA'];
    private const INITIAL_ADDRESS = 'SA';
    private const INITIAL_EMAIL = 'test@amwal.tech';
    private const INITIAL_PHONE = '966500000000';
    private const PLUGIN_VERSION = '1.0.0';
    private const CART_ID = 'cart_id';
    private const INITIAL_FIRST_NAME = 'Amwal';
    private const INITIAL_LAST_NAME = 'Tester';

    /**
     * @var AmwalButtonConfigInterface
     */
    private $amwalButtonConfigInterface;

    protected function setUp(): void
    {
        $this->amwalButtonConfigInterface = $this->createMock(AmwalButtonConfigInterface::class);
    }

    /**
     * Test getter method for Merchant ID.
     */
    public function testGetMerchantId()
    {
        $this->amwalButtonConfigInterface->method('getMerchantId')->willReturn(self::MERCHANT_ID);
        $this->assertEquals(self::MERCHANT_ID, $this->amwalButtonConfigInterface->getMerchantId());
    }

    /**
     * Test getter method for Amount.
     */
    public function testGetAmount()
    {
        $this->amwalButtonConfigInterface->method('getAmount')->willReturn(self::AMOUNT);
        $this->assertEquals(self::AMOUNT, $this->amwalButtonConfigInterface->getAmount());
    }

    /**
     * Test getter method for Installment Options Url.
     */
    public function testGetInstallmentOptionsUrl()
    {
        $this->amwalButtonConfigInterface->method('getInstallmentOptionsUrl')->willReturn(self::INSTALLMENT_OPTIONS_URL);
        $this->assertEquals(self::INSTALLMENT_OPTIONS_URL, $this->amwalButtonConfigInterface->getInstallmentOptionsUrl());
    }

    /**
     * Test getter method for Test Environment.
     */
    public function testGetTestEnvironment()
    {
        $this->amwalButtonConfigInterface->method('getTestEnvironment')->willReturn(self::TEST_ENVIRONMENT);
        $this->assertEquals(self::TEST_ENVIRONMENT, $this->amwalButtonConfigInterface->getTestEnvironment());
    }

    /**
     * Test getter method for Enable Pre Checkout Trigger.
     */
    public function testGetEnablePreCheckoutTrigger()
    {
        $this->amwalButtonConfigInterface->method('getEnablePreCheckoutTrigger')->willReturn(self::ENABLE_PRE_CHECKOUT_TRIGGER);
        $this->assertEquals(self::ENABLE_PRE_CHECKOUT_TRIGGER, $this->amwalButtonConfigInterface->isEnablePreCheckoutTrigger());
    }

    /**
     * Test getter method for Enable Pre Pay Trigger.
     */
    public function testGetEnablePrePayTrigger()
    {
        $this->amwalButtonConfigInterface->method('getEnablePrePayTrigger')->willReturn(self::ENABLE_PRE_PAY_TRIGGER);
        $this->assertEquals(self::ENABLE_PRE_PAY_TRIGGER, $this->amwalButtonConfigInterface->isEnablePrePayTrigger());
    }

    /**
     * Test getter method for Show Payment Brands.
     */
    public function testGetShowPaymentBrands()
    {
        $this->amwalButtonConfigInterface->method('getShowPaymentBrands')->willReturn(self::SHOW_PAYMENT_BRANDS);
        $this->assertEquals(self::SHOW_PAYMENT_BRANDS, $this->amwalButtonConfigInterface->isShowPaymentBrands());
    }

    /**
     * Test getter method for Disabled.
     */
    public function testGetDisabled()
    {
        $this->amwalButtonConfigInterface->method('getDisabled')->willReturn(self::DISABLED);
        $this->assertEquals(self::DISABLED, $this->amwalButtonConfigInterface->isDisabled());
    }

    /**
     * Test getter method for Label.
     */
    public function testGetLabel()
    {
        $this->amwalButtonConfigInterface->method('getLabel')->willReturn(self::LABEL);
        $this->assertEquals(self::LABEL, $this->amwalButtonConfigInterface->getLabel());
    }

    /**
     * Test getter method for Ref Id.
     */
    public function testGetRefId()
    {
        $this->amwalButtonConfigInterface->method('getRefId')->willReturn(self::REF_ID);
        $this->assertEquals(self::REF_ID, $this->amwalButtonConfigInterface->getRefId());
    }

    /**
     * Test getter method for Address Handshake.
     */
    public function testGetAddressHandshake()
    {
        $this->amwalButtonConfigInterface->method('getAddressHandshake')->willReturn(self::ADDRESS_HANDSHAKE);
        $this->assertEquals(self::ADDRESS_HANDSHAKE, $this->amwalButtonConfigInterface->hasAddressHandshake());
    }

    /**
     * Test getter method for Address Required.
     */
    public function testGetAddressRequired()
    {
        $this->amwalButtonConfigInterface->method('getAddressRequired')->willReturn(self::ADDRESS_REQUIRED);
        $this->assertEquals(self::ADDRESS_REQUIRED, $this->amwalButtonConfigInterface->isAddressRequired());
    }

    /**
     * Test getter method for Email Required.
     */
    public function testGetEmailRequired()
    {
        $this->amwalButtonConfigInterface->method('getEmailRequired')->willReturn(self::EMAIL_REQUIRED);
        $this->assertEquals(self::EMAIL_REQUIRED, $this->amwalButtonConfigInterface->isEmailRequired());
    }

    /**
     * Test getter method for Dark Mode.
     */
    public function testGetDarkMode()
    {
        $this->amwalButtonConfigInterface->method('getDarkMode')->willReturn(self::DARK_MODE);
        $this->assertEquals(self::DARK_MODE, $this->amwalButtonConfigInterface->getDarkMode());
    }

    /**
     * Test getter method for Country Code.
     */
    public function testGetCountryCode()
    {
        $this->amwalButtonConfigInterface->method('getCountryCode')->willReturn(self::COUNTRY_CODE);
        $this->assertEquals(self::COUNTRY_CODE, $this->amwalButtonConfigInterface->getCountryCode());
    }

    /**
     * Test getter method for Allowed Address Countries.
     */
    public function testGetAllowedAddressCountries()
    {
        $this->amwalButtonConfigInterface->method('getAllowedAddressCountries')->willReturn(self::ALLOWED_ADDRESS_COUNTRIES);
        $this->assertEquals(self::ALLOWED_ADDRESS_COUNTRIES, $this->amwalButtonConfigInterface->getAllowedAddressCountries());
    }

    /**
     * Test getter method for Postcode Optional Countries.
     */
    public function testGetPostcodeOptionalCountries()
    {
        $this->amwalButtonConfigInterface->method('getPostcodeOptionalCountries')->willReturn(self::POSTCODE_OPTIONAL_COUNTRIES);
        $this->assertEquals(self::POSTCODE_OPTIONAL_COUNTRIES, $this->amwalButtonConfigInterface->getPostcodeOptionalCountries());
    }

    /**
     * Test getter method for Allowed Address States.
     */
    public function testGetAllowedAddressStates()
    {
        $this->amwalButtonConfigInterface->method('getAllowedAddressStates')->willReturn(json_encode(self::ALLOWED_ADDRESS_STATES));
        $this->assertEquals(json_encode(self::ALLOWED_ADDRESS_STATES), $this->amwalButtonConfigInterface->getAllowedAddressStates());
    }

    /**
     * Test getter method for Allowed Address Cities.
     */
    public function testGetAllowedAddressCities()
    {
        $this->amwalButtonConfigInterface->method('getAllowedAddressCities')->willReturn(json_encode(self::ALLOWED_ADDRESS_CITIES));
        $this->assertEquals(json_encode(self::ALLOWED_ADDRESS_CITIES), $this->amwalButtonConfigInterface->getAllowedAddressCities());
    }

    /**
     * Test getter method for Initial Address.
     */
    public function testGetInitialAddress()
    {
        $this->amwalButtonConfigInterface->method('getInitialAddress')->willReturn(self::INITIAL_ADDRESS);
        $this->assertEquals(self::INITIAL_ADDRESS, $this->amwalButtonConfigInterface->getInitialAddress());
    }

    /**
     * Test getter method for Initial Email.
     */
    public function testGetInitialEmail()
    {
        $this->amwalButtonConfigInterface->method('getInitialEmail')->willReturn(self::INITIAL_EMAIL);
        $this->assertEquals(self::INITIAL_EMAIL, $this->amwalButtonConfigInterface->getInitialEmail());
    }

    /**
     * Test getter method for Initial Phone.
     */
    public function testGetInitialPhone()
    {
        $this->amwalButtonConfigInterface->method('getInitialPhone')->willReturn(self::INITIAL_PHONE);
        $this->assertEquals(self::INITIAL_PHONE, $this->amwalButtonConfigInterface->getInitialPhone());
    }

    /**
     * Test getter method for Plugin Version.
     */
    public function testGetPluginVersion()
    {
        $this->amwalButtonConfigInterface->method('getPluginVersion')->willReturn(self::PLUGIN_VERSION);
        $this->assertEquals(self::PLUGIN_VERSION, $this->amwalButtonConfigInterface->getPluginVersion());
    }

    /**
     * Test getter method for Cart Id.
     */
    public function testGetCartId()
    {
        $this->amwalButtonConfigInterface->method('getCartId')->willReturn(self::CART_ID);
        $this->assertEquals(self::CART_ID, $this->amwalButtonConfigInterface->getCartId());
    }

    /**
     * Test getter method for Initial First Name.
     */
    public function testGetInitialFirstName()
    {
        $this->amwalButtonConfigInterface->method('getInitialFirstName')->willReturn(self::INITIAL_FIRST_NAME);
        $this->assertEquals(self::INITIAL_FIRST_NAME, $this->amwalButtonConfigInterface->getInitialFirstName());
    }

    /**
     * Test getter method for Initial Last Name.
     */
    public function testGetInitialLastName()
    {
        $this->amwalButtonConfigInterface->method('getInitialLastName')->willReturn(self::INITIAL_LAST_NAME);
        $this->assertEquals(self::INITIAL_LAST_NAME, $this->amwalButtonConfigInterface->getInitialLastName());
    }
}
