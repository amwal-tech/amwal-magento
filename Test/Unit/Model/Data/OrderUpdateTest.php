<?php
declare(strict_types=1);

namespace Amwal\Payments\Test\Unit\Model\Data;

use PHPUnit\Framework\TestCase;
use Amwal\Payments\Model\Data\OrderUpdate;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Amwal\Payments\Model\GetAmwalOrderData;
use Amwal\Payments\Model\Config;
use Magento\Sales\Model\OrderNotifier;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Amwal\Payments\Model\Checkout\InvoiceOrder;
use Psr\Log\LoggerInterface;
use Amwal\Payments\Model\AmwalClientFactory;
use Amwal\Payments\Plugin\Sentry\SentryExceptionReport;
use Magento\Framework\DataObject;
use Magento\Framework\Mail\Transport;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderUpdateTest extends TestCase
{
    private $orderRepository;
    private $storeManager;
    private $getAmwalOrderData;
    private $config;
    private $orderNotifier;
    private $transportFactory;
    private $orderUpdate;
    private const AMWAL_ORDER_ID = '6e369835-451c-4071-8d86-496bd4a19eb6';
    private const ORDER_ID = '000000001';
    private const CURRENCY_CODE = 'SAR';
    private const BASE_URL = 'http://example.com/';
    private const ORDER_URL = self::BASE_URL . 'sales/order/view/order_id/' . self::ORDER_ID;

    protected function setUp(): void
    {
        // Mock necessary dependencies
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->transportFactory = $this->createMock(TransportInterfaceFactory::class);
        $sentryExceptionReport = $this->createMock(SentryExceptionReport::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);

        // Initialize other dependencies
        $this->getAmwalOrderData = $this->createMock(GetAmwalOrderData::class);
        $this->config = $this->createMock(Config::class);

        // Pass the mock objects to OrderUpdate
        $this->orderNotifier = $this->createMock(OrderNotifier::class);
        $message = $this->createMock(MessageInterface::class);
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $invoiceAmwalOrder = $this->createMock(InvoiceOrder::class);
        $logger = $this->createMock(LoggerInterface::class);
        $amwalClientFactory = $this->createMock(AmwalClientFactory::class);

        $orderUpdate = new OrderUpdate(
            $this->orderRepository,
            $this->storeManager,
            $this->getAmwalOrderData,
            $this->config,
            $this->orderNotifier,
            $this->transportFactory,
            $message,
            $scopeConfig,
            $invoiceAmwalOrder,
            $logger,
            $amwalClientFactory,
            $sentryExceptionReport
        );
        $this->orderUpdate = $orderUpdate;
    }

    /**
     * @dataProvider dataProviderTestUpdateTrigger
     */
    public function testUpdateSuccess($trigger)
    {
        // Mock Order object
        $order = $this->getMockBuilder(Order::class)
            ->addMethods(['getAmwalOrderId'])
            ->onlyMethods(['getState', 'getGrandTotal', 'getBaseGrandTotal', 'getOrderCurrencyCode', 'getIncrementId', 'hasInvoices', 'addCommentToStatusHistory'])
            ->disableOriginalConstructor()
            ->getMock();

        $store = $this->createMockStore();
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        $order->expects($this->any())
            ->method('getAmwalOrderId')
            ->willReturn(self::AMWAL_ORDER_ID);

        // Mock GetAmwalOrderData result
        $amwalOrderData = $this->createPartialMock(DataObject::class, ['getStatus', 'getTotalAmount']);
        $amwalOrderData->method('getStatus')->willReturn('success');
        $amwalOrderData->method('getTotalAmount')->willReturn(100.00);
        $this->getAmwalOrderData->method('execute')->willReturn($amwalOrderData);

        // Mock Amwal order data validation result
        $this->config->method('getOrderConfirmedStatus')->willReturn('order_confirmed_status');
        $this->getAmwalOrderData->method('execute')->willReturn($amwalOrderData);

        // Mock isPayValid result
        $this->config->method('getOrderConfirmedStatus')->willReturn('order_confirmed_status');
        $this->config->method('isOrderStatusChangedCustomerEmailEnabled')->willReturn(true);
        $this->config->method('isOrderStatusChangedAdminEmailEnabled')->willReturn(true);
        $order->method('getState')->willReturn('pending_payment');
        $order->method('getGrandTotal')->willReturn(100.00);
        $order->method('getBaseGrandTotal')->willReturn(100.00);
        $order->method('getOrderCurrencyCode')->willReturn(self::CURRENCY_CODE);
        $order->method('getAmwalOrderId')->willReturn(self::AMWAL_ORDER_ID);
        $order->method('getIncrementId')->willReturn(self::ORDER_ID);
        $order->method('hasInvoices')->willReturn(true);

        $amwalOrderId = $order->getAmwalOrderId();
        $status = $amwalOrderData->getStatus();
        if($trigger == 'PendingOrdersUpdate') {
            $historyComment = __('Successfully completed Amwal payment with transaction ID %1 By Pending Orders Cron Job', $amwalOrderId);
        } elseif($trigger == 'CanceledOrdersUpdate') {
            $historyComment = __('Successfully completed Amwal payment with transaction ID %1 By Canceled Orders Cron Job', $amwalOrderId);
        } elseif($trigger == 'AmwalOrderDetails') {
            $historyComment = __('Order status updated to (%1) by Amwal Payments webhook', $status);
        } elseif($trigger == 'PayOrder') {
            $historyComment = __('Successfully completed Amwal payment with transaction ID: %1', $amwalOrderId);
        } else {
            $historyComment = __('Order status updated to (%1) by Amwal Payments', $status);
        }
        $order->expects($this->once())->method('addCommentToStatusHistory')->with($historyComment);

        // Expectations
        $this->orderRepository->expects($this->once())->method('save')->with($order);
        $this->orderNotifier->expects($this->once())->method('notify')->with($order);
        $this->transportFactory->expects($this->once())->method('create')->willReturn($this->createMock(Transport::class));

        // Assert the result
        $result = $this->orderUpdate->update($order, $trigger, true);
        $this->assertInstanceOf(DataObject::class, $result);
    }

    public function testIsPayValid()
    {
        // Mock Order object
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock Config object
        $config = $this->createMock(Config::class);
        $config->method('getOrderConfirmedStatus')->willReturn(Order::STATE_PROCESSING);
        $this->config->expects($this->any())->method('getOrderConfirmedStatus')->willReturn(Order::STATE_PROCESSING);

        $order->expects($this->any())
            ->method('getIncrementId')
            ->willReturn(self::ORDER_ID);

        // Test when order state is 'pending_payment'
        $order->method('getState')->willReturn(Order::STATE_PENDING_PAYMENT);
        $this->assertTrue($this->orderUpdate->isPayValid($order));

        // Test when order state is 'canceled'
        $order->method('getState')->willReturn(Order::STATE_CANCELED);
        $this->assertTrue($this->orderUpdate->isPayValid($order));

        // Test when order state is not in valid states
        $order->method('getState')->willReturn(Order::STATE_COMPLETE);
        $this->assertEquals(
            sprintf('Order (%s) is not in a valid state to be updated (%s)', $order->getIncrementId(), Order::STATE_COMPLETE),
            $this->orderUpdate->isPayValid($order)
        );
    }
    public function testGetOrderUrl()
    {
        // Mock Order object
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $order->expects($this->any())
            ->method('getEntityId')
            ->willReturn(self::ORDER_ID);

        // Expectations
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->createMockStore());

        // Execute the method
        $result = $this->orderUpdate->getOrderUrl($order);
        $this->assertEquals(self::ORDER_URL, $result);
    }

    private function createMockStore()
    {
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->addMethods(['getBaseUrl'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $storeMock->method('getBaseUrl')->willReturn(self::BASE_URL);
        return $storeMock;
    }

    /**
     * @dataProvider dataProviderTestUpdateTrigger
     */
    public function dataProviderTestUpdateTrigger()
    {
        return [
            ['PendingOrdersUpdate'],
            ['AmwalOrderDetails'],
            ['PayOrder'],
            ['OrderUpdate'],
        ];
    }
}
