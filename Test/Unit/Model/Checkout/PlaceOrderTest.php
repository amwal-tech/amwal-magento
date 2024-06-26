<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\Unit\Model\Checkout;

use Amwal\Payments\Model\Checkout\PlaceOrder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceOrderTest extends TestCase
{
    private $placeOrder;
    private $quoteRepositoryMock;
    private $quoteManagementMock;
    private $quoteAddressFactoryMock;
    private $orderRepositoryMock;
    private $quoteMock;
    private $searchCriteriaBuilderMock;

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
    private const REF_ID = '1f80146ddd68d71f9064af90d1afc83ccdc99e13595afcfce60dea15be8b7ec4';
    private const AMWAL_ORDER_ID = '6e369835-451c-4071-8d86-496bd4a19eb6';
    private const AMWAL_ORDER_ID_CANCELED = '6e369835-451c-4071-8d86-496bd4a19eb6-canceled';
    private const TRIGGER_CONTEXT = 'cart';


    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        // Mocking the Quote class with proper initialization
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->setMethods([
                'setCustomerEmail',
                'setBillingAddress',
                'setShippingAddress',
                'getBillingAddress',
                'getShippingAddress',
                'getCustomerEmail',
                'getId'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteMock->method('getCustomerEmail')->willReturn(self::EMAIL);
        $this->quoteMock->method('getBillingAddress')->willReturn($this->createMockAddress());
        $this->quoteMock->method('getShippingAddress')->willReturn($this->createMockAddress());

        $this->quoteRepositoryMock = $this->createMock(QuoteRepository::class);
        $this->quoteManagementMock = $this->createMock(QuoteManagement::class);
        $this->quoteAddressFactoryMock = $this->createMock(AddressFactory::class);
        $this->orderRepositoryMock = $this->createMock(OrderRepository::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);


        $this->placeOrder = $objectManager->getObject(
            PlaceOrder::class,
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'quoteManagement' => $this->quoteManagementMock,
                'quoteAddressFactory' => $this->quoteAddressFactoryMock,
                'orderRepository' => $this->orderRepositoryMock,
                'logger' => $loggerMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock
            ]
        );
    }

    /**
     * Test createOrder method
     */
    public function testCreateOrder()
    {
        // Mock Quote
        $this->quoteMock->method('getId')->willReturn(1);

        // Mock Order
        $orderMock = $this->createPartialMock(Order::class, ['getState', 'getEntityId', 'cancel', 'setAmwalOrderId','setAmwalTriggerContext', 'getAmwalOrderId', 'setRefId', 'getRefId', 'addCommentToStatusHistory']);
        $orderMock->method('getState')->willReturn(Order::STATE_PENDING_PAYMENT);
        $orderMock->method('getEntityId')->willReturn(2);
        $orderMock->method('getAmwalOrderId')->willReturn(self::AMWAL_ORDER_ID);
        $orderMock->method('setAmwalTriggerContext')->willReturn(self::TRIGGER_CONTEXT);
        $orderMock->method('getRefId')->willReturn(self::REF_ID);
        $orderMock->method('setRefId')->with(self::REF_ID)->willReturnSelf();
        $statusHistoryMessage = 'Amwal Transaction ID: ' . self::AMWAL_ORDER_ID;
        $orderMock->method('addCommentToStatusHistory')->with($statusHistoryMessage)->willReturnSelf();

        $this->orderRepositoryMock->method('getList')->willReturn($this->createOrderList([]));

        // Set expectations for Quote Management mock
        $this->quoteManagementMock
            ->method('placeOrder')
            ->willReturn(1);

        $this->orderRepositoryMock
            ->method('get')
            ->willReturn($orderMock);

        // Set expectations for Order Repository mock
        $this->orderRepositoryMock
            ->method('save')
            ->with($orderMock)
            ->willReturn($orderMock);

        $this->searchCriteriaBuilderMock
            ->method('addFilter')
            ->with('amwal_order_id', self::AMWAL_ORDER_ID)
            ->willReturnSelf();

        $this->searchCriteriaBuilderMock
            ->method('create')
            ->willReturn($this->createMock(SearchCriteriaInterface::class));

        // Call the method to be tested
        $resultOrder = $this->placeOrder->createOrder($this->quoteMock, self::AMWAL_ORDER_ID, self::REF_ID, self::TRIGGER_CONTEXT);

        // Assertions
        $this->assertSame($orderMock, $resultOrder);
        $this->assertEquals(Order::STATE_PENDING_PAYMENT, $orderMock->getState());
        $this->assertEquals(Order::STATE_PENDING_PAYMENT, $orderMock->getStatus());
        $this->assertEquals(self::AMWAL_ORDER_ID, $orderMock->getAmwalOrderId());
        $this->assertEquals(self::REF_ID, $orderMock->getRefId());
    }

    /**
     * Test updateCustomerAddress method
     */
    public function testUpdateCustomerAddress()
    {
        // Create a mock for AddressInterface with specified methods
        $customerAddress = $this->createMock(AddressInterface::class);

        // Create a mock for QuoteAddress
        $quoteAddress = $this->createMock(Address::class);

        // Set expectations for the QuoteAddressFactory mock
        $this->quoteAddressFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($quoteAddress);

        // Set expectations for the Quote mock
        $this->quoteMock->method('setCustomerEmail')
            ->with(self::EMAIL);

        // Set expectations for the QuoteAddress mock
        $quoteAddress->expects($this->once())
            ->method('importCustomerAddressData')
            ->with($customerAddress);

        $quoteAddress->expects($this->once())
            ->method('setEmail')
            ->with(self::EMAIL);

        // Set expectations for the QuoteRepository mock
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock);

        // Call the method to be tested
        $this->placeOrder->updateCustomerAddress($this->quoteMock, $customerAddress);
    }


    /**
     * Test setCustomerEmail method
     */
    public function testSetCustomerEmail()
    {
        // Set expectations for the Quote mock
        $this->quoteMock->expects($this->once())
            ->method('setCustomerEmail')
            ->with(self::EMAIL);

        // Set expectations for the QuoteRepository mock
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock);

        // Call the method to be tested
        $this->placeOrder->setCustomerEmail($this->quoteMock, self::EMAIL);

        // Assert that the customer email is set correctly in the Quote and its addresses
        $this->assertEquals(self::EMAIL, $this->quoteMock->getCustomerEmail());

        $billingAddress = $this->quoteMock->getBillingAddress();
        $this->assertEquals(self::EMAIL, $billingAddress->getEmail());

        $shippingAddress = $this->quoteMock->getShippingAddress();
        $this->assertEquals(self::EMAIL, $shippingAddress->getEmail());
    }

    /**
     * Create a mock address
     *
     * @return Address
     */
    private function createMockAddress(): Address
    {
        $addressMock = $this->createMock(Address::class);

        $addressMock->method('getFirstname')->willReturn(self::FIRST_NAME);
        $addressMock->method('getLastname')->willReturn(self::LAST_NAME);
        $addressMock->method('getTelephone')->willReturn(self::PHONE_NUMBER);
        $addressMock->method('getEmail')->willReturn(self::EMAIL);
        $addressMock->method('getPostcode')->willReturn(self::POSTCODE);
        $addressMock->method('getCountryId')->willReturn(self::COUNTRY);
        $addressMock->method('getCity')->willReturn(self::CITY);
        $addressMock->method('getRegion')->willReturn(self::STATE);
        $addressMock->method('getStreet')->willReturn([self::STREET_1, self::STREET_2]);

        return $addressMock;
    }

    /**
     * Helper method to create a mock order list for the repository
     */
    private function createOrderList(array $orders): OrderSearchResultInterface
    {
        $orderList = $this->getMockBuilder(OrderSearchResultInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderList->method('getItems')->willReturn($orders);

        return $orderList;
    }
}
