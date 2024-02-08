<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\Unit\Model\Checkout;

use Amwal\Payments\Model\Checkout\PayOrder;
use Amwal\Payments\Model\Checkout\PlaceOrder;
use Amwal\Payments\Model\GetAmwalOrderData;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\ErrorReporter;
use Amwal\Payments\Plugin\Sentry\SentryExceptionReport;
use Amwal\Payments\Model\Data\OrderUpdate;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Model\Order\CustomerManagement;
use Magento\Sales\Model\Order;
use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Repository as PaymentRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Throwable;

class PayOrderTest extends TestCase
{
    /**
     * @var PayOrder
     */
    private $payOrder;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $quoteRepository;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $checkoutSession;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $orderRepository;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $messageManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentRepository;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerManagement;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerRepository;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $orderUpdate;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $config;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $sentryExceptionReport;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerSession;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $getAmwalOrderData;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $errorReporter;

    private const AMWAL_ORDER_ID = '6e369835-451c-4071-8d86-496bd4a19eb6';
    private const ORDER_ID = 1;
    private const CURRENCY_CODE = 'SAR';
    private const CUSTOMER_ID = 1;
    private const CUSTOMER_EMAIL = 'test@example.com';
    private const FIRST_NAME = 'Tester';
    private const LAST_NAME = 'Amwal';
    private const PHONE_NUMBER = '+95512345678';
    private const POSTCODE = '12345';
    private const COUNTRY = 'SA';
    private const CITY = "Riyadh";
    private const STATE = 'Riyadh';
    private const STREET_1 = 'Street 123';
    private const STREET_2 = '12345, Region';

    protected function setUp(): void
    {
        // Create mock objects
        $this->quoteRepository = $this->createMock(CartRepositoryInterface::class);
        $this->checkoutSession = $this->createMock(CheckoutSession::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);
        $this->orderUpdate = $this->createMock(OrderUpdate::class);
        $this->config = $this->createMock(Config::class);
        $this->sentryExceptionReport = $this->createMock(SentryExceptionReport::class);
        $this->paymentRepository = $this->createMock(OrderPaymentRepositoryInterface::class);
        $this->customerManagement = $this->createMock(CustomerManagement::class);
        $this->customerRepository = $this->createMock(CustomerRepositoryInterface::class);
        $this->customerSession = $this->createMock(CustomerSession::class);
        $this->getAmwalOrderData = $this->createMock(GetAmwalOrderData::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->errorReporter = $this->createMock(ErrorReporter::class);

        // Create the PayOrder instance with injected mock objects
        $this->payOrder = new PayOrder(
            $this->quoteRepository,
            $this->checkoutSession,
            $this->getAmwalOrderData,
            $this->orderRepository,
            $this->messageManager,
            $this->paymentRepository,
            $this->customerRepository,
            $this->customerSession,
            $this->customerManagement,
            $this->errorReporter,
            $this->config,
            $this->logger,
            $this->orderUpdate,
            $this->sentryExceptionReport
        );
    }

    public function testUpdateCustomerName(): void
    {
        // Mock order and amwalOrderData
        $orderMock = $this->createMock(OrderInterface::class);
        $amwalOrderDataMock = $this->createPartialMock(DataObject::class, ['getClientFirstName', 'getClientLastName']);
        $amwalOrderDataMock->method('getClientFirstName')->willReturn('John');
        $amwalOrderDataMock->method('getClientLastName')->willReturn('Doe');

        // Set expectations
        $orderMock->expects($this->once())->method('setCustomerFirstname')->with('John');
        $orderMock->expects($this->once())->method('setCustomerLastname')->with('Doe');
        $this->orderRepository->expects($this->once())->method('save')->with($orderMock);

        // Call the method
        $this->payOrder->updateCustomerName($orderMock, $amwalOrderDataMock);
    }


    public function testAddError(): void
    {
        // Mock message
        $errorMessage = 'Custom error message';

        // Set expectations
        $this->messageManager->expects($this->once())->method('addErrorMessage')->with($errorMessage);

        // Call the method with a custom error message
        $this->payOrder->addError($errorMessage);
    }

    public function testAddErrorWithDefaultMessage(): void
    {
        // Set expectations
        $this->messageManager->expects($this->once())->method('addErrorMessage')->with(
            __('Something went wrong while placing your order. Please contact us to complete the order.')
        );

        // Call the method without passing a custom error message
        $this->payOrder->addError();
    }

    public function testAddAdditionalPaymentInformation(): void
    {
        // Mock order and amwal order data
        $orderMock = $this->createMock(OrderInterface::class);
        $amwalOrderDataMock = $this->createPartialMock(DataObject::class, ['getData']);
        $amwalOrderDataMock->method('getData')->willReturn('Amwal');

        // Mock payment and additional information
        $paymentMock = $this->createMock(Payment::class);
        $additionalInfo = ['payment_brand' => 'Amwal'];

        // Set expectations
        $orderMock->expects($this->once())->method('getPayment')->willReturn($paymentMock);
        $paymentMock->expects($this->once())->method('getAdditionalInformation')->willReturn($additionalInfo);
        $paymentMock->expects($this->once())->method('setAdditionalInformation')->with($additionalInfo);
        $this->paymentRepository->expects($this->once())->method('save')->with($paymentMock);
        $this->orderRepository->expects($this->once())->method('save')->with($orderMock);

        // Call the method
        $this->payOrder->addAdditionalPaymentInformation($amwalOrderDataMock, $orderMock);
    }

    public function testCreateCustomer(): void
    {
        // Mock order
        $orderMock = $this->createMock(OrderInterface::class);
        $orderMock->method('getQuoteId')->willReturn(self::ORDER_ID);

        // Mock quote
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->quoteRepository->expects($this->once())
            ->method('get')
            ->with(self::ORDER_ID)
            ->willReturn($quoteMock);

        // Mock customer
        $customerMock = $this->getMockBuilder(CustomerInterface::class)
            ->addMethods(['__toArray'])
            ->getMockForAbstractClass();

        $customerMock->method('__toArray')
            ->willReturn([
                'firstname' => self::FIRST_NAME,
                'lastname' => self::LAST_NAME,
                'country_id' => self::COUNTRY,
                'city' => self::CITY,
                'postcode' => self::POSTCODE,
                'street' => [self::STREET_1, self::STREET_2],
                'telephone' => self::PHONE_NUMBER,
                'email' => self::CUSTOMER_EMAIL
            ]);
        $this->customerManagement
            ->expects($this->once())
            ->method('create')
            ->with(self::ORDER_ID)
            ->willReturn($customerMock);


        // Call the method
        $customer = $this->payOrder->createCustomer($orderMock);

        // Assertions
        $this->assertInstanceOf(CustomerInterface::class, $customer);
    }

    public function testCustomerWithEmailExistsWhenCustomerExists(): void
    {
        // Mock email
        $email = 'test@example.com';

        // Set expectations
        $this->customerRepository->expects($this->once())
            ->method('get')
            ->with($email);

        // Call the method
        $result = $this->payOrder->customerWithEmailExists($email);

        // Assert
        $this->assertTrue($result);
    }

    public function testCustomerWithEmailExistsWhenCustomerDoesNotExist(): void
    {
        // Mock email
        $email = 'nonexistent@example.com';

        // Set expectations
        $this->customerRepository->expects($this->once())
            ->method('get')
            ->with($email)
            ->willThrowException(new NoSuchEntityException());

        // Call the method
        $result = $this->payOrder->customerWithEmailExists($email);

        // Assert
        $this->assertFalse($result);
    }

    public function testCustomerWithEmailExistsWhenErrorOccurs(): void
    {
        // Set expectations
        $this->customerRepository->expects($this->once())
            ->method('get')
            ->with(self::CUSTOMER_EMAIL)
            ->willThrowException(new LocalizedException(__('An error occurred.')));

        // Call the method
        $result = $this->payOrder->customerWithEmailExists(self::CUSTOMER_EMAIL);

        // Assert
        $this->assertFalse($result);
    }

    public function testShouldCreateCustomer(): void
    {
        // Mock order and amwalOrderData
        $orderMock = $this->createMock(OrderInterface::class);
        $amwalOrderDataMock = $this->createPartialMock(DataObject::class, ['getClientEmail']);

        // Set expectations for getClientEmail method
        $amwalOrderDataMock->method('getClientEmail')->willReturn(self::CUSTOMER_EMAIL);

        // Mock the customer repository to return false when customer does not exist
        $this->customerRepository->expects($this->once())
            ->method('get')
            ->with(self::CUSTOMER_EMAIL)
            ->willThrowException(new NoSuchEntityException(__('Customer not found')));

        // Mock config method shouldCreateCustomer
        $this->config->method('shouldCreateCustomer')->willReturn(true);

        // Set expectation for getCustomerIsGuest method
        $orderMock->expects($this->once())
            ->method('getCustomerIsGuest')
            ->willReturn(true);

        // Call the method
        $result = $this->payOrder->shouldCreateCustomer($orderMock, $amwalOrderDataMock);

        // Assert
        $this->assertTrue($result);
    }
}
