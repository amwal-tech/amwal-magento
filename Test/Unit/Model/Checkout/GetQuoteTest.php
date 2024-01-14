<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\Unit\Model\Checkout;

use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Api\RefIdManagementInterface;
use Amwal\Payments\Model\Checkout\GetQuote;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\ErrorReporter;
use Amwal\Payments\Model\AddressResolver;
use Amwal\Payments\Plugin\Sentry\SentryExceptionReport;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface as QuoteRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Phrase;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\ShippingMethodManagement;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Api\AttributeValue;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\DataObject\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class GetQuoteTest extends TestCase
{
    private $getQuote;
    private $objectManager;
    private $refIdManagement;
    private $quoteRepository;
    private $logger;
    private $quoteFactory;
    private $storeManager;
    private $config;
    private $quoteIdMaskFactory;
    private $cartRepository;
    private $shippingMethodManagement;
    private $checkoutSession;
    private $addressResolver;
    private $customerRepository;
    private $sentryExceptionReport;
    private $productRepository;
    private $customerFactory;
    private $customerSession;
    private $objectFactory;

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
    private const TOTAL = 100.0;
    private const TOTAL_TAX = 10.0;
    private const QUOTE_ID = 123;
    private const REF_ID = '1f80146ddd68d71f9064af90d1afc83ccdc99e13595afcfce60dea15be8b7ec4';
    private const IS_GUEST_QUOTE = false;
    private const AMWAL_ADDRESS_ID = '6e369835-451c-4071-8d86-496bd4a19eb6';
    private const TRIGGER_CONTEXT = 'cart';
    private const ORDER_ITEMS = [];

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        // Create mock objects for dependencies
        $this->refIdManagement = $this->createMock(RefIdManagementInterface::class);
        $this->quoteRepository = $this->createMock(QuoteRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->quoteFactory = $this->createMock(QuoteFactory::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->config = $this->createMock(Config::class);
        $this->quoteIdMaskFactory = $this->createMock(QuoteIdMaskFactory::class);
        $this->cartRepository = $this->createMock(CartInterface::class);
        $this->shippingMethodManagement = $this->createMock(ShippingMethodManagement::class);
        $this->checkoutSession = $this->createMock(CheckoutSession::class);
        $this->addressResolver = $this->createMock(AddressResolver::class);
        $this->customerRepository = $this->createMock(CustomerRepositoryInterface::class);
        $this->sentryExceptionReport = $this->createMock(SentryExceptionReport::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->customerFactory = $this->createMock(CustomerFactory::class);
        $this->customerSession = $this->createMock(CustomerSession::class);
        $this->objectFactory = $this->createMock(Factory::class);

        $this->getQuote = $this->objectManager->getObject(
            GetQuote::class,
            [
                'refIdManagement' => $this->refIdManagement,
                'quoteRepository' => $this->quoteRepository,
                'logger' => $this->logger,
                'quoteFactory' => $this->quoteFactory,
                'storeManager' => $this->storeManager,
                'config' => $this->config,
                'quoteIdMaskFactory' => $this->quoteIdMaskFactory,
                'cartRepository' => $this->cartRepository,
                'shippingMethodManagement' => $this->shippingMethodManagement,
                'checkoutSession' => $this->checkoutSession,
                'addressResolver' => $this->addressResolver,
                'customerRepository' => $this->customerRepository,
                'sentryExceptionReport' => $this->sentryExceptionReport,
                'productRepository' => $this->productRepository,
                'customerFactory' => $this->customerFactory,
                'customerSession' => $this->customerSession,
                'objectFactory' => $this->objectFactory
            ]
        );
    }

    /**
     * Test the createQuote method.
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testCreateQuote()
    {
        return "";
        // Mock data and parameters
        $orderItems = [
            $this->createMockOrderItem('123', 2),
            $this->createMockOrderItem('456', 1),
        ];
        $customer = $this->createMockCustomer();

        // Set expectations for the mock objects
        $this->storeManager->method('getStore')->willReturn($this->createMockStore());
        $this->customerSession->method('getCustomer')->willReturn($customer);

        $customer->method('getGroupId')->willReturn(0);
        $quoteMock = $this->createMockQuote();

        $quoteMock->expects($this->once())
            ->method('setCustomerGroupId')
            ->with($customer->getGroupId());

        // Mock product repository calls
        $this->productRepository->expects($this->exactly(2))
            ->method('getById')
            ->withConsecutive(['123'], ['456'])
            ->willReturnOnConsecutiveCalls(
                $this->createMockProduct('123'),
                $this->createMockProduct('456')
            );

        // Mock quote and quote repository calls
        $this->quoteFactory->expects($this->once())
            ->method('create')
            ->willReturn($quoteMock);

        $validRequest = $this->getMockBuilder(DataObject::class)
            ->onlyMethods(['setData'])
            ->getMock();

        $validRequest->method('setData')->willReturnSelf();

        $this->objectFactory->method('create')->willReturn($validRequest);

        $this->quoteRepository->expects($this->once())
            ->method('save');

        // Assertions based on the expected result
        $result = $this->getQuote->createQuote($orderItems);
        $this->assertInstanceOf(Quote::class, $result);
    }

    /**
     * Helper method to create a mock order item.
     *
     * @param string $productId
     * @param int $qty
     * @return DataObject|MockObject
     */
    private function createMockOrderItem(string $productId, int $qty)
    {
        $item = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getProductId', 'getQty'])
            ->getMock();

        $item->expects($this->any())
            ->method('getProductId')
            ->willReturn($productId);
        $item->expects($this->any())
            ->method('getQty')
            ->willReturn($qty);

        return $item;
    }

    /**
     * Helper method to create a mock product.
     *
     * @param string $productId
     * @return MockObject
     */
    private function createMockProduct(string $productId): MockObject
    {
        $productMock = $this->createMock(Product::class);
        $productMock->method('getId')->willReturn($productId);

        return $productMock;
    }

    /**
     * Helper method to create a mock quote.
     *
     * @return MockObject
     */
    private function createMockQuote(): MockObject
    {
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['setCustomerGroupId'])
            ->disableOriginalConstructor()
            ->getMock();

        return $quoteMock;
    }

    /**
     * Helper method to create a mock store.
     *
     * @return MockObject
     */
    private function createMockStore(): MockObject
    {
        return $this->createMock(Store::class);
    }

    /**
     * Helper method to create a mock customer.
     *
     * @return MockObject
     */
    private function createMockCustomer(): MockObject
    {
        return $this->createMock(Customer::class);
    }

    /**
     * Test the getCustomerAddress method.
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testGetCustomerAddress()
    {
        // Mock data and parameters
        $amwalOrderDataMock = $this->getMockBuilder(DataObject::class)->getMock();

        // Mock AddressInterface object
        $customerAddressMock = $this->getMockBuilder(AddressInterface::class)
            ->addMethods(['__toArray'])
            ->getMockForAbstractClass();

        $customerAddressMock->method('__toArray')->willReturn([
            'firstname' => self::FIRST_NAME,
            'lastname' => self::LAST_NAME,
            'country_id' => self::COUNTRY,
            'city' => self::CITY,
            'postcode' => self::POSTCODE,
            'street' => [self::STREET_1, self::STREET_2],
            'telephone' => self::PHONE_NUMBER,
            'custom_attributes' => [
                'amwal_address_id' => new AttributeValue(['attribute_code' => 'amwal_address_id', 'value' => self::AMWAL_ADDRESS_ID])
            ]
        ]);

        // Set expectations for the mock objects
        $this->addressResolver->expects($this->once())
            ->method('execute')
            ->with($amwalOrderDataMock, self::IS_GUEST_QUOTE)
            ->willReturn($customerAddressMock);

        // Assertions based on the expected result
        $result = $this->getQuote->getCustomerAddress($amwalOrderDataMock, self::REF_ID, self::IS_GUEST_QUOTE);
        $this->assertInstanceOf(AddressInterface::class, $result);
    }

    /**
     * Test the getQuote method.
     */
    public function testGetQuote()
    {
        // Mock data and parameters
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Set expectations for the mock objects
        $this->checkoutSession->method('getQuote')->willReturn(null);

        $quoteIdMaskMock = $this->getMockBuilder(QuoteIdMask::class)
            ->disableOriginalConstructor()
            ->addMethods(['getMaskedId'])
            ->onlyMethods(['load'])
            ->getMock();

        $this->quoteIdMaskFactory->method('create')->willReturn($quoteIdMaskMock);

        $quoteIdMaskMock->method('load')->willReturnSelf();
        $quoteIdMaskMock->method('getMaskedId')->willReturn('masked_id');

        $this->quoteRepository->expects($this->once())
            ->method('get')
            ->with(self::QUOTE_ID)
            ->willReturn($quoteMock);

        // Assertions based on the expected result
        $result = $this->getQuote->getQuote(self::QUOTE_ID, self::ORDER_ITEMS, self::TRIGGER_CONTEXT);
        $this->assertInstanceOf(Quote::class, $result);
    }

    /**
     * Test the getAvailableRates method.
     */
    public function testGetAvailableRates()
    {
        // Mock data and parameters
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->onlyMethods(['getId', 'getShippingAddress'])
            ->disableOriginalConstructor()
            ->getMock();

        $addressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ratesMock = [
            $this->createRateMock('carrier_code_1', 'method_code_1', 'Method Title 1', 10.0),
            $this->createRateMock('carrier_code_2', 'method_code_2', 'Method Title 2', 15.0),
        ];

        // Set expectations for the mock objects
        $quoteMock->method('getId')->willReturn(self::QUOTE_ID);
        $quoteMock->method('getShippingAddress')->willReturn($addressMock);

        $this->shippingMethodManagement->expects($this->once())
            ->method('estimateByExtendedAddress')
            ->with($quoteMock->getId(), $addressMock)
            ->willReturn($ratesMock);

        // Assertions based on the expected result
        $result = $this->getQuote->getAvailableRates($quoteMock);
        $expectedRates = [
            'carrier_code_1_method_code_1' => ['carrier_title' => 'Method Title 1', 'price' => '10.00'],
            'carrier_code_2_method_code_2' => ['carrier_title' => 'Method Title 2', 'price' => '15.00'],
        ];
        $this->assertEquals($expectedRates, $result);
    }

    /**
     * Helper method to create a mock for a shipping rate.
     *
     * @param string $carrierCode
     * @param string $methodCode
     * @param string $methodTitle
     * @param float $priceInclTax
     * @return MockObject
     */
    private function createRateMock(string $carrierCode, string $methodCode, string $methodTitle, float $priceInclTax): MockObject
    {
        $rateMock = $this->getMockBuilder(ShippingMethodInterface::class)
            ->onlyMethods(['getCarrierCode', 'getMethodCode', 'getMethodTitle', 'getPriceInclTax'])
            ->getMockForAbstractClass();

        $rateMock->method('getCarrierCode')->willReturn($carrierCode);
        $rateMock->method('getMethodCode')->willReturn($methodCode);
        $rateMock->method('getMethodTitle')->willReturn($methodTitle);
        $rateMock->method('getPriceInclTax')->willReturn($priceInclTax);

        return $rateMock;
    }

    /**
     * Test the getResponseData method.
     */
    public function testGetResponseData()
    {
        // Mock data and parameters
        $quoteMock = $this->getMockBuilder(CartInterface::class)
            ->addMethods([
                'getGrandTotal',
                'getShippingAddress',
                'getTotals',
                'getData',
                'getBaseGrandTotal',
                'getAmount',
                'getSubtotal',
                'getAdditionalFeeAmount',
                'getAdditionalFeeDescription'
            ])
            ->onlyMethods(['getId'])
            ->getMockForAbstractClass();

        $shippingAddressMock = $this->getMockBuilder(Address::class)
            ->addMethods([
                'getBaseTaxAmount',
                'getTaxAmount',
                'getBaseShippingInclTax',
                'getShippingInclTax',
                'getBaseDiscountAmount',
                'getDiscountAmount',
                'getBaseSubtotalTotalInclTax',
                'getSubtotalInclTax'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $availableRates = ['rate1', 'rate2'];

        // Set expectations for the mock objects
        $quoteMock->method('getShippingAddress')->willReturn($shippingAddressMock);
        $quoteMock->method('getId')->willReturn(123);
        $quoteMock->method('getBaseGrandTotal')->willReturn(100.0);
        $quoteMock->method('getAmount')->willReturn(100.0);
        $quoteMock->method('getSubtotal')->willReturn(95.0);
        $quoteMock->method('getAdditionalFeeAmount')->willReturn(0.0);
        $quoteMock->method('getAdditionalFeeDescription')->willReturn('');

        $shippingAddressMock->method('getBaseTaxAmount')->willReturn(5.0);
        $shippingAddressMock->method('getTaxAmount')->willReturn(5.0);
        $shippingAddressMock->method('getBaseShippingInclTax')->willReturn(10.0);
        $shippingAddressMock->method('getShippingInclTax')->willReturn(10.0);
        $shippingAddressMock->method('getBaseDiscountAmount')->willReturn(-2.0);
        $shippingAddressMock->method('getDiscountAmount')->willReturn(-2.0);
        $shippingAddressMock->method('getBaseSubtotalTotalInclTax')->willReturn(100.0);
        $shippingAddressMock->method('getSubtotalInclTax')->willReturn(100.0);

        $this->config->method('shouldUseBaseCurrency')->willReturn(true);

        $quoteIdMaskMock = $this->getMockBuilder(QuoteIdMask::class)
            ->disableOriginalConstructor()
            ->addMethods(['getMaskedId'])
            ->onlyMethods(['load'])
            ->getMock();

        $this->quoteIdMaskFactory->method('create')->willReturn($quoteIdMaskMock);

        $quoteIdMaskMock->method('load')->willReturnSelf();
        $quoteIdMaskMock->method('getMaskedId')->willReturn('masked_id');

        // Assertions based on the expected result
        $result = $this->getQuote->getResponseData($quoteMock, $availableRates);
        $expectedResult = [
            'cart_id' => 'masked_id',
            'available_rates' => $availableRates,
            'amount' => 100.0,
            'subtotal' => 95.0,
            'tax_amount' => 5.0,
            'shipping_amount' => 10.0,
            'discount_amount' => 2.0,
            'additional_fee_amount' => 0.0,
            'additional_fee_description' => '',
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test the getAdditionalFeeAmount method.
     */
    public function testGetAdditionalFeeAmount()
    {
        // Mock data and parameters
        $quoteMock = $this->getMockBuilder(CartInterface::class)
            ->addMethods(['getTotals'])
            ->getMockForAbstractClass();

        $totals = [
            'amasty_extrafee' => $this->getMockBuilder(Total::class)
                ->addMethods(['getValueInclTax'])
                ->getMock(),
        ];

        // Set expectations for the mock objects
        $quoteMock->method('getTotals')->willReturn($totals);
        $totals['amasty_extrafee']->method('getValueInclTax')->willReturn(self::TOTAL + self::TOTAL_TAX);

        // Assertions based on the expected result
        $result = $this->getQuote->getAdditionalFeeAmount($quoteMock);
        $this->assertEquals(self::TOTAL + self::TOTAL_TAX, $result);
    }

    /**
     * Test the getAdditionalFeeDescription method.
     */
    public function testGetAdditionalFeeDescription()
    {
        // Mock data and parameters
        $quoteMock = $this->getMockBuilder(CartInterface::class)
            ->addMethods(['getTotals', 'getData'])
            ->getMockForAbstractClass();

        $totals = [
            'amasty_extrafee' => $this->getMockBuilder(Total::class)
                ->addMethods(['getTitle'])
                ->getMock(),
        ];

        // Set expectations for the mock objects
        $quoteMock->method('getTotals')->willReturn($totals);
        $quoteMock->method('getData')->with('applied_amasty_fee_flag')->willReturn(true);
        $titleMock = $this->getMockBuilder(Phrase::class)->setConstructorArgs(['Fee Description'])->getMock();
        $titleMock->method('getArguments')->willReturn(['Fee Description']);

        $totals['amasty_extrafee']->method('getTitle')->willReturn($titleMock);

        // Assertions based on the expected result
        $result = $this->getQuote->getAdditionalFeeDescription($quoteMock);
        $this->assertEquals('Fee Description', $result);
    }

    /**
     * Test the getAmount method.
     */
    public function testGetAmount()
    {
        // Mock data and parameters
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getBaseGrandTotal', 'getGrandTotal'])
            ->onlyMethods(['getTotals', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();

        $totals = [
            'amasty_extrafee' => $this->getMockBuilder(Total::class)
                ->addMethods(['getValueInclTax'])
                ->disableOriginalConstructor()
                ->getMock(),
        ];

        // Set expectations for the mock objects
        $quoteMock->method('getBaseGrandTotal')->willReturn(self::TOTAL);
        $quoteMock->method('getGrandTotal')->willReturn(self::TOTAL + self::TOTAL_TAX);
        $quoteMock->method('getTotals')->willReturn($totals);
        $quoteMock->method('getData')->with('applied_amasty_fee_flag')->willReturn(true);
        $totals['amasty_extrafee']->method('getValueInclTax')->willReturn(self::TOTAL_TAX);

        // Assertions based on the expected result
        $true_base_currency_result = $this->getQuote->getAmount($quoteMock, true);
        $this->assertEquals(self::TOTAL - self::TOTAL_TAX, $true_base_currency_result);

        $false_base_currency_result = $this->getQuote->getAmount($quoteMock, false);
        $this->assertEquals(self::TOTAL, $false_base_currency_result);
    }


    /**
     * Test the getSubtotal method.
     */
    public function testGetSubtotal()
    {
        // Mock data and parameters
        $shippingAddressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['getBaseSubtotalTotalInclTax', 'getSubtotalInclTax'])
            ->disableOriginalConstructor()
            ->getMock();

        // Set expectations for the mock objects
        $total = self::TOTAL + self::TOTAL_TAX;
        $shippingAddressMock->method('getBaseSubtotalTotalInclTax')->willReturn($total);
        $shippingAddressMock->method('getSubtotalInclTax')->willReturn($total);

        // Assertions based on the expected result
        $true_base_currency_result = $this->getQuote->getSubtotal($shippingAddressMock, self::TOTAL_TAX, true);
        $this->assertEquals(self::TOTAL, $true_base_currency_result);

        $false_base_currency_result = $this->getQuote->getSubtotal($shippingAddressMock, self::TOTAL_TAX, false);
        $this->assertEquals(self::TOTAL, $false_base_currency_result);
    }


    /**
     * Create a mock address
     *
     * @return Address
     */
    private function createMockAddress(): Address
    {
        $addressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['getRegionCode', 'getStreetLine', 'getEmail'])
            ->onlyMethods(['getFirstname', 'getLastname', 'getTelephone', 'getPostcode', 'getCountryId', 'getCity', 'getStreet'])
            ->disableOriginalConstructor()
            ->getMock();

        $addressMock->method('getFirstname')->willReturn(self::FIRST_NAME);
        $addressMock->method('getLastname')->willReturn(self::LAST_NAME);
        $addressMock->method('getTelephone')->willReturn(self::PHONE_NUMBER);
        $addressMock->method('getPostcode')->willReturn(self::POSTCODE);
        $addressMock->method('getCountryId')->willReturn(self::COUNTRY);
        $addressMock->method('getCity')->willReturn(self::CITY);
        $addressMock->method('getStreet')->willReturn([self::STREET_1, self::STREET_2]);
        $addressMock->method('getRegionCode')->willReturn(self::STATE);
        $addressMock->method('getStreetLine')->willReturn(self::STREET_1);
        $addressMock->method('getEmail')->willReturn(self::EMAIL);

        return $addressMock;
    }
}
