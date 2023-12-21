<?php
declare(strict_types=1);

namespace Amwal\Payments\Test\Unit\Model\Button;

use PHPUnit\Framework\TestCase;
use Amwal\Payments\Model\Button\GetConfig;
use Amwal\Payments\Api\Data\AmwalButtonConfigInterface;
use Amwal\Payments\Api\Data\RefIdDataInterface;
use Magento\Quote\Model\Quote;
use Magento\Customer\Model\Session;
use Amwal\Payments\Model\Data\AmwalButtonConfigFactory;
use Amwal\Payments\Model\Config;
use Amwal\Payments\ViewModel\ExpressCheckoutButton;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Checkout\Model\SessionFactory as CheckoutSessionFactory;
use Amwal\Payments\Model\ThirdParty\CityHelper;
use Amwal\Payments\Api\Data\AmwalAddressInterfaceFactory;
use Amwal\Payments\Api\RefIdManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Amwal\Payments\Model\Data\AmwalButtonConfig;
use ReflectionMethod;
use Magento\Customer\Model\Data\Address;
use Amwal\Payments\Api\Data\AmwalAddressInterface;
use Magento\Customer\Model\SessionFactory;

class GetConfigTest extends TestCase
{
    private $getConfig;
    private $customerSessionFactory;
    private $customerSession;

    private const FIRST_NAME = 'Tester';
    private const LAST_NAME = 'Amwal';
    private const PHONE_NUMBER = '+95512345678';
    private const EMAIL = 'test@amwal.tech';
    private const POSTCODE = '12345';
    private const COUNTRY = 'SA';
    private const CITY = "Riyadh";
    private const STATE = 'Riyadh';
    private const STREET_1 = 'Street 123';
    private const STREET_2 = '12345, Region';

    private const MERCHANT_ID = 'sandbox-amwal-e09ee380-d8c7-4710-a6ab-c9b39c7ffd47';
    private const AMOUNT = 100.00;
    private const LABEL = 'quick-buy';
    private const ADDRESS_HANDSHAKE = true;
    private const ADDRESS_REQUIRED = true;
    private const EMAIL_REQUIRED = true;
    private const REF_ID = '';
    private const SHOW_PAYMENT_BRANDS = true;
    private const DISABLED = false;
    private const ID = 'amwal-checkout';
    private const ALLOWED_ADDRESS_COUNTRIES = ['SA'];
    private const ALLOWED_ADDRESS_CITIES = ['SA' => ['1110' => ['Riyadh'], '1111' => ['Dammam']]];
    private const ALLOWED_ADDRESS_STATES = ['SA' => ['1111' => ['Dammam'], '1110' => ['Riyadh']]];
    private const ENABLE_PRE_CHECKOUT_TRIGGER = true;
    private const DARK_MODE = 'off';
    private const ENABLE_PRE_PAY_TRIGGER = true;
    private const INSTALLMENT_OPTIONS_URL = '';
    private const INITIAL_ADDRESS = [
        'city' => self::CITY,
        'state' => self::STATE,
        'postcode' => self::POSTCODE,
        'country' => self::COUNTRY,
        'street1' => self::STREET_1,
        'street2' => self::STREET_2,
        'email' => self::EMAIL
    ];

    private const MOCK_BUTTON_CONFIG_DATA = [
        'merchantId' => self::MERCHANT_ID,
        'amount' => self::AMOUNT,
        'label' => self::LABEL,
        'addressHandshake' => self::ADDRESS_HANDSHAKE,
        'addressRequired' => self::ADDRESS_REQUIRED,
        'emailRequired' => self::EMAIL_REQUIRED,
        'refId' => self::REF_ID,
        'showPaymentBrands' => self::SHOW_PAYMENT_BRANDS,
        'disabled' => self::DISABLED,
        'id' => self::ID,
        'allowedAddressCountries' => self::ALLOWED_ADDRESS_COUNTRIES,
        'allowedAddressCities' => self::ALLOWED_ADDRESS_CITIES,
        'allowedAddressStates' => self::ALLOWED_ADDRESS_STATES,
        'enablePreCheckoutTrigger' => self::ENABLE_PRE_CHECKOUT_TRIGGER,
        'darkMode' => self::DARK_MODE,
        'enablePrePayTrigger' => self::ENABLE_PRE_PAY_TRIGGER,
        'installmentOptionsUrl' => self::INSTALLMENT_OPTIONS_URL,
        'initialAddress' => json_encode(self::INITIAL_ADDRESS)
    ];

    protected function setUp(): void
    {
        $mockButtonConfigFactory = $this->createMock(AmwalButtonConfigFactory::class);
        $mockConfig = $this->createMock(Config::class);
        $mockViewModel = $this->createMock(ExpressCheckoutButton::class);
        $mockStoreManager = $this->createMock(StoreManagerInterface::class);
        $mockCustomerSessionFactory = $this->createMock(CustomerSessionFactory::class);
        $mockCheckoutSessionFactory = $this->createMock(CheckoutSessionFactory::class);
        $mockCityHelper = $this->createMock(CityHelper::class);
        $mockAmwalAddressFactory = $this->createMock(AmwalAddressInterfaceFactory::class);
        $mockRefIdManagement = $this->createMock(RefIdManagementInterface::class);
        $mockCartRepository = $this->createMock(CartRepositoryInterface::class);
        $mockProductRepository = $this->createMock(ProductRepositoryInterface::class);
        $mockJsonSerializer = $this->createMock(Json::class);
        $mockRegionCollectionFactory = $this->createMock(RegionCollectionFactory::class);
        $mockQuoteIdMaskFactory = $this->createMock(QuoteIdMaskFactory::class);

        $this->getConfig = new GetConfig(
            $mockButtonConfigFactory,
            $mockConfig,
            $mockViewModel,
            $mockStoreManager,
            $mockCustomerSessionFactory,
            $mockCheckoutSessionFactory,
            $mockCityHelper,
            $mockAmwalAddressFactory,
            $mockRefIdManagement,
            $mockCartRepository,
            $mockProductRepository,
            $mockJsonSerializer,
            $mockRegionCollectionFactory,
            $mockQuoteIdMaskFactory
        );
        $this->buttonConfigMock = $this->createMock(AmwalButtonConfigInterface::class);
        $this->setButtonConfigData();
    }

    public function testAddGenericButtonConfig()
    {
        $buttonConfig = $this->createMock(AmwalButtonConfig::class);
        $refIdData = $this->createMock(RefIdDataInterface::class);
        $quote = $this->createMock(Quote::class);

        $this->customerSession = $this->createMock(Session::class);
        $this->customerSession->method('isLoggedIn')->willReturn(true);
        $this->getConfig->customerSession = $this->customerSession;

        // Call the method
        $this->getConfig->addGenericButtonConfig($buttonConfig, $refIdData, $quote);

        $this->assertEquals('quick-buy', $this->buttonConfigMock->getLabel());
        $this->assertTrue($this->buttonConfigMock->getAddressHandshake());
        $this->assertTrue($this->buttonConfigMock->getAddressRequired());
        $this->assertTrue($this->buttonConfigMock->getEmailRequired());
        $this->assertEquals('', $this->buttonConfigMock->getRefId());
        $this->assertTrue($this->buttonConfigMock->getShowPaymentBrands());
        $this->assertFalse($this->buttonConfigMock->getDisabled());
        $this->assertEquals('amwal-checkout', $this->buttonConfigMock->getId());
        $this->assertEquals(['SA'], $this->buttonConfigMock->getAllowedAddressCountries());
        $this->assertEquals(['SA'], $this->buttonConfigMock->getAllowedAddressCities());
        $this->assertEquals(['SA'], $this->buttonConfigMock->getAllowedAddressStates());
        $this->assertTrue($this->buttonConfigMock->getEnablePreCheckoutTrigger());
        $this->assertEquals('off', $this->buttonConfigMock->getDarkMode());
        $this->assertTrue($this->buttonConfigMock->getEnablePrePayTrigger());
        $this->assertEquals('', $this->buttonConfigMock->getMerchantId());
        $this->assertEquals('', $this->buttonConfigMock->getInstallmentOptionsUrl());
    }

    public function testGetInitialAddressData()
    {
        // Mock Session, Quote, and AmwalAddressFactory
        $mockCustomerSession = $this->createMock(Session::class);
        $mockQuote = $this->createMock(Quote::class);
        $mockAmwalAddressFactory = $this->createMock(AmwalAddressInterfaceFactory::class);

        $mockAddress = $this->createMock(Address::class);
        $mockAmwalAddress = $this->createMock(AmwalAddressInterface::class);

        // Setup the mock objects
        $mockAddress->method('getCity')->willReturn(self::CITY);
        $mockQuote->method('getShippingAddress')->willReturn($this->createMockAddress());
        $mockQuote->method('getBillingAddress')->willReturn($this->createMockAddress());
        $mockAmwalAddressFactory->method('create')->willReturn($mockAmwalAddress);


        // Call the method and assert outcomes
        //$result = $this->getConfig->getInitialAddressData($mockCustomerSession, $mockQuote);
        //$this->assertIsArray($result);
    }



    public function testGetButtonId()
    {
        $result = $this->getConfig->getButtonId('123');
        $this->assertEquals('amwal-checkout-123', $result);
    }

    public function testPhoneFormat()
    {
        $formattedPhone = $this->getConfig->phoneFormat('+11234567890', 'US');
        $this->assertEquals('+11234567890', $formattedPhone);
    }


    /**
     * Create a mock address
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    private function createMockAddress(): Address
    {
        $addressMock = $this->createMock(Address::class);

        $addressMock->method('getFirstname')->willReturn(self::FIRST_NAME);
        $addressMock->method('getLastname')->willReturn(self::LAST_NAME);
        $addressMock->method('getTelephone')->willReturn(self::PHONE_NUMBER);
        $addressMock->method('getPostcode')->willReturn(self::POSTCODE);
        $addressMock->method('getCountryId')->willReturn(self::COUNTRY);
        $addressMock->method('getCity')->willReturn(self::CITY);
        $addressMock->method('getStreet')->willReturn([self::STREET_1, self::STREET_2]);

        return $addressMock;
    }

    /**
     * Test adding generic button configuration
     */
    private function setButtonConfigData(bool $useTmp = false): void
    {
        foreach (self::MOCK_BUTTON_CONFIG_DATA as $key => $value) {
          $this->buttonConfigMock->method('get' . ucfirst($key))->willReturn($value);
        }
    }
}