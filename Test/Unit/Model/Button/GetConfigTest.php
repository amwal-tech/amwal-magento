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
    }

    public function testAddGenericButtonConfig()
    {
        // Create mock objects for the dependencies that need to be injected
        $buttonConfig = $this->createMock(AmwalButtonConfig::class);
        $refIdData = $this->createMock(RefIdDataInterface::class);
        $quote = $this->createMock(Quote::class);

        // Call the method
        $this->getConfig->addGenericButtonConfig($buttonConfig, $refIdData, $quote);

        // Assert that the properties of $buttonConfig have been set correctly
        $this->assertEquals('quick-buy', $buttonConfig->getLabel());
        $this->assertTrue($buttonConfig->getAddressHandshake());
        // Add more assertions for other properties as needed
    }


    public function testGetInitialAddressData()
    {
        // Mock Session, Quote, and AmwalAddressFactory
        $mockCustomerSession = $this->createMock(Session::class);
        $mockQuote = $this->createMock(Quote::class);
        $mockAmwalAddressFactory = $this->createMock(AmwalAddressInterfaceFactory::class);

        // Mock Address and AmwalAddress (or the implementation of AmwalAddressInterface)
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
}
