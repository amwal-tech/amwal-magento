<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\Integration;

use Amwal\Payments\Api\Data\AmwalButtonConfigInterface;
use Amwal\Payments\Api\RefIdManagementInterface;
use Amwal\Payments\Model\AddressResolver;
use Amwal\Payments\Model\Button\GetCartButtonConfig;
use Amwal\Payments\Model\Checkout\GetQuote;
use Amwal\Payments\Model\Checkout\PayOrder;
use Amwal\Payments\Model\Checkout\PlaceOrder;
use Amwal\Payments\Model\Checkout\SetAmwalOrderDetails;
use Amwal\Payments\Model\Checkout\UpdateShippingMethod;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\ErrorReporter;
use Amwal\Payments\Model\GetAmwalOrderData;
use Amwal\Payments\Plugin\Sentry\SentryExceptionReport;
use Amwal\Payments\Model\Settings;
use Amwal\Payments\Cron\PendingOrdersUpdate;
use Amwal\Payments\Cron\CanceledOrdersUpdate;
use Amwal\Payments\Plugin\Sales\Order\SalesOrderGridPlugin;
use Exception;
use JsonException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface as QuoteRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use TddWizard\Fixtures\Checkout\CartBuilder;

/**
 * Tests the full checkout flow consisting of
 *    - Retrieving button configuration
 *    - Create Amwal transaction
 *    - Set Amwal transaction data (Phone number, Address, Shipping)
 *    - Retrieving Quote
 *    - Placing Order
 *    - Paying Order
 *    - Settings
 *    - Pending Orders Cron Job
 *    - Canceled Orders Cron Job
 *    - Sales Order Grid Plugin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckoutFlowTest extends IntegrationTestBase
{
    private const BUTTON_CONFIG_EXPECTED_KEYS = [
        'merchant_id', 'amount', 'country_code', 'dark_mode', 'email_required',
        'address_required', 'address_handshake', 'ref_id', 'label', 'disabled',
        'show_payment_brands', 'enable_pre_checkout_trigger', 'enable_pre_pay_trigger',
        'id', 'test_environment', 'allowed_address_countries', 'allowed_address_states',
        'plugin_version', 'postcode_optional_countries',
        'show_discount_ribbon', 'discount'
    ];

    private const GET_QUOTE_EXPECTED_KEYS = [
        'cart_id', 'available_rates', 'amount', 'subtotal', 'tax_amount', 'shipping_amount',
        'discount_amount', 'additional_fee_amount', 'additional_fee_description'
    ];

    /**
     * @var GetCartButtonConfig|null
     */
    private ?GetCartButtonConfig $getCartButtonConfig = null;

    /**
     * @var QuoteIdMaskFactory |null
     */
    private ?QuoteIdMaskFactory $quoteIdMaskFactory = null;

    /**
     * @var GetQuote|null
     */
    private ?GetQuote $getQuote = null;

    /**
     * @var PlaceOrder&LegacyMockInterface&MockInterface|null
     */
    private $placeOrderMock = null;

    /**
     * @var PayOrder|null
     */
    private ?PayOrder $payOrder = null;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->getCartButtonConfig = $this->objectManager->get(GetCartButtonConfig::class);
        $this->quoteIdMaskFactory = $this->objectManager->get(QuoteIdMaskFactory::class);
        $this->getQuote = $this->objectManager->get(GetQuote::class);
        $this->payOrder = $this->objectManager->get(PayOrder::class);

        $this->placeOrderMock = Mockery::mock(
            PlaceOrder::class,
            [
                $this->objectManager->get(QuoteManagement::class),
                $this->objectManager->get(AddressFactory::class),
                $this->objectManager->get(QuoteRepositoryInterface::class),
                $this->objectManager->get(ManagerInterface::class),
                $this->objectManager->get(AddressResolver::class),
                $this->objectManager->get(OrderRepositoryInterface::class),
                $this->objectManager->get(RefIdManagementInterface::class),
                $this->objectManager->get(UpdateShippingMethod::class),
                $this->objectManager->get(SetAmwalOrderDetails::class),
                $this->objectManager->get(MaskedQuoteIdToQuoteIdInterface::class),
                $this->objectManager->get(GetAmwalOrderData::class),
                $this->objectManager->get(ErrorReporter::class),
                $this->objectManager->get(SentryExceptionReport::class),
                $this->objectManager->get(Config::class),
                $this->objectManager->get(LoggerInterface::class),
                $this->objectManager->get(SearchCriteriaBuilder::class),
                $this->objectManager->get(StoreManagerInterface::class),
                $this->objectManager->get(Collection::class)
            ]
        )->makePartial();

        $this->placeOrderMock->shouldReceive('verifyRefId')
            ->andReturn(true);
    }

    /**
     * @covers GetCartButtonConfig::execute
     *
     * @return array
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testGetCartButtonConfig(): array
    {
        $cart = CartBuilder::forCurrentSession()
            ->withSimpleProduct(
                self::MOCK_PRODUCT_SKU
            )
            ->build();

        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $quoteIdMask->setQuoteId((int) $cart->getQuote()->getId())->save();
        $cartId = $quoteIdMask->getMaskedId();

        $refIdData = $this->getMockRefIdData();

        /** /V1/amwal/button/cart */
        $buttonConfig = $this->getCartButtonConfig->execute(
            $refIdData,
            'product-detail-page',
            $cartId
        );

        $this->assertTrue(is_a($buttonConfig, AmwalButtonConfigInterface::class));
        $this->assertNotEmpty($buttonConfig->getMerchantId());

        $response = $buttonConfig->toArray();

        foreach (self::BUTTON_CONFIG_EXPECTED_KEYS as $key) {
            $this->assertArrayHasKey($key, $response);
        }

        $this->assertIsString($response['merchant_id']);
        $this->assertIsNumeric($response['amount']);
        $this->assertGreaterThan(0, $response['amount']);

        return [$buttonConfig, $cartId];
    }

    /**
     * @covers  GetQuote::execute
     * @depends testGetCartButtonConfig
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @param array $dependencies
     *
     * @return array
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException|JsonException
     */
    public function testGetQuote(array $dependencies): array
    {
        /** @var AmwalButtonConfigInterface $buttonConfig */
        [$buttonConfig, $cartId] = $dependencies;

        $amwalTransactionData = $this->getAmwalTransaction($buttonConfig);
        $this->assertIsArray($amwalTransactionData);
        $this->assertArrayHasKey('id', $amwalTransactionData, 'Amwal Transaction did not return a transaction ID');
        $this->assertArrayHasKey('address_details', $amwalTransactionData, 'Amwal Transaction did not return address details');
        $this->assertIsArray($amwalTransactionData['address_details']);

        $addressData = [
            'street1' => $amwalTransactionData['address_details']['street1'],
            'country' => $amwalTransactionData['address_details']['country'],
            'city' => $amwalTransactionData['address_details']['city'],
            'state' => $amwalTransactionData['address_details']['state'],
            'postcode' => $amwalTransactionData['address_details']['postcode'],
            'client_phone_number' => $amwalTransactionData['client_phone_number'],
            'client_email' => $amwalTransactionData['client_email'],
            'client_first_name' => $amwalTransactionData['client_first_name'],
            'client_last_name' => $amwalTransactionData['client_last_name'],
            'orderId' => $amwalTransactionData['id'],
        ];

        /** /V1/amwal/get-quote */
        $quoteResponse = $this->getQuote->execute(
            [],
            $buttonConfig->getRefId(),
            $this->getMockRefIdData(),
            $addressData,
            false,
            $cartId
        );

        $this->assertIsArray($quoteResponse);
        $this->assertArrayHasKey('data', $quoteResponse);

        $quoteResponse = $quoteResponse['data'];

        // Perform assertions
        foreach (self::GET_QUOTE_EXPECTED_KEYS as $key) {
            $this->assertArrayHasKey($key, $quoteResponse);
        }

        // Validate specific values if needed
        $this->assertIsNumeric($quoteResponse['amount']);
        $this->assertGreaterThan(0, $quoteResponse['amount']);

        $this->assertIsNumeric($quoteResponse['subtotal']);
        $this->assertGreaterThan(0, $quoteResponse['subtotal']);

        return [$cartId, $amwalTransactionData, $addressData];
    }

    /**
     * @covers PlaceOrder::execute
     * @depends testGetQuote
     */
    public function testPlaceOrder(array $dependencies): OrderInterface
    {
        /** @var AmwalButtonConfigInterface $buttonConfig */
        [$cartId, $amwalTransactionData, $addressData] = $dependencies;

        /** /V1/amwal/place-order */
        $order = $this->placeOrderMock->execute(
            $addressData,
            $cartId,
            self::MOCK_REF_ID,
            $this->getMockRefIdData(),
            $amwalTransactionData['id'],
            'test-case',
            true,
            "545454"
        );

        $this->assertTrue(is_a($order, OrderInterface::class));

        // Perform assertions
        $this->assertEquals('pending_payment', $order->getState());
        $this->assertNotEmpty($order->getEntityId());
        $this->assertNotEmpty($order->getAmwalOrderId());

        return $order;
    }

    /**
     * @covers PayOrder::execute
     * @depends testPlaceOrder
     *
     * @param OrderInterface $order
     *
     * @return void
     * @throws LocalizedException
     */
    public function testPayOrder(OrderInterface $order): void
    {
        /** /V1/amwal/pay-order */
        $response = $this->payOrder->execute(
            (int) $order->getEntityId(),
            $order->getAmwalOrderId()
        );

        $this->assertIsBool($response);
        $this->assertTrue($response);
    }

    /**
     * @covers Settings::getSettings
     *
     * @return void
     * @throws JsonException
     */
    public function testGetSettings(): void
    {
        /** @var Settings $settings */
        $settings = $this->objectManager->get(Settings::class);

        $response = $settings->getSettings();

        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);

        $settingsData = $response['data'];

        $this->assertIsArray($settingsData);
        $this->assertArrayHasKey('amwal_payment', $settingsData);
        $this->assertIsBool($settingsData['amwal_payment']);
    }

    /**
     * @covers PendingOrdersUpdate::execute
     *
     * @return void
     * @throws JsonException
     */
    public function testPendingOrdersUpdate(): void
    {
        /** @var PendingOrdersUpdate $pendingOrdersUpdate */
        $pendingOrdersUpdate = $this->objectManager->get(PendingOrdersUpdate::class);

        $pendingOrdersUpdate->execute();
    }


    /**
     * @covers CanceledOrdersUpdate::execute
     *
     * @return void
     * @throws JsonException
     */
    public function testCanceledOrdersUpdate(): void
    {
        /** @var CanceledOrdersUpdate $canceledOrdersUpdate */
        $canceledOrdersUpdate = $this->objectManager->get(CanceledOrdersUpdate::class);

        $canceledOrdersUpdate->execute();
    }


    /**
     * @covers SalesOrderGridPlugin::beforeLoad
     *
     * @return void
     */
    public function testSalesOrderGridPlugin(): void
    {
        /** @var Collection $collection */
        $collection = $this->objectManager->get(Collection::class);

        $firstItem = $collection->getFirstItem();
        $this->assertArrayHasKey('amwal_order_id', $firstItem->getData(), 'amwal_order_id is not present in the order grid collection');
        $this->assertArrayHasKey('amwal_trigger_context', $firstItem->getData(), 'amwal_trigger_context is not present in the order grid collection');
    }


    /**
     * Amwal pop-up - Generate a transaction on button press
     *
     * @param AmwalButtonConfigInterface $buttonConfig
     *
     * @return array
     * @throws JsonException
     */
    private function getAmwalTransaction(AmwalButtonConfigInterface $buttonConfig): array
    {
        return $this->executeAmwalCall(
            'https://qa-backend.sa.amwal.tech/transactions/' . self::MOCK_TRANSACTION_ID,
            [],
            $buttonConfig->getMerchantId(),
            'GET'
        );
    }
}
