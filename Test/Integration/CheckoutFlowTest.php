<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\Integration;

use Amwal\Payments\Api\Data\AmwalButtonConfigInterface;
use Amwal\Payments\Model\Button\GetCartButtonConfig;
use Amwal\Payments\Model\Checkout\GetQuote;
use Amwal\Payments\Model\Checkout\PayOrder;
use Amwal\Payments\Model\Checkout\PlaceOrder;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\Config\Checkout\ConfigProvider;
use Amwal\Payments\Model\GetAmwalOrderData;
use Amwal\Payments\Model\Data\OrderUpdate;
use Amwal\Payments\Model\Settings;
use Amwal\Payments\Cron\PendingOrdersUpdate;
use Amwal\Payments\Cron\CanceledOrdersUpdate;
use Amwal\Payments\Block\Adminhtml\Order\View\Tab\AmwalTab;
use Amwal\Payments\Block\Product\View\Promotion;
use Exception;
use JsonException;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
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
 *    - Amwal Tab Block
 *    - Promotion Block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckoutFlowTest extends IntegrationTestBase
{
    private const BUTTON_CONFIG_EXPECTED_KEYS = [
        'merchant_id', 'amount', 'country_code', 'dark_mode', 'email_required',
        'address_required', 'address_handshake', 'ref_id', 'label', 'disabled',
        'show_payment_brands', 'enable_pre_checkout_trigger', 'enable_pre_pay_trigger',
        'id', 'allowed_address_countries', 'allowed_address_states',
        'plugin_version', 'postcode_optional_countries', 'installment_options_url',
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
     * @var PayOrder|null
     */
    private ?PayOrder $payOrder = null;

    /**
     * @var PlaceOrder|null
     */
    private ?PlaceOrder $placeOrder = null;

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
        $this->placeOrder = $this->objectManager->get(PlaceOrder::class);
    }

    /**
     * @covers \Amwal\Payments\Model\Button\GetCartButtonConfig::execute
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
     * @covers \Amwal\Payments\Model\Checkout\GetQuote::execute
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

        // Define address data directly (same values used to create the transaction)
        $addressData = [
            'street1' => '123 Test Street',
            'country' => 'SA',
            'city' => 'Riyadh',
            'state' => 'Riyadh Province',
            'postcode' => '12345',
            'client_phone_number' => '+966501234567',
            'client_email' => 'test@example.com',
            'client_first_name' => 'Test',
            'client_last_name' => 'User',
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

        // Create the Amwal transaction with the actual quote total (including shipping)
        // so that dataValidation in PayOrder/PendingOrdersUpdate passes.
        // The quote amount excludes shipping because no rate is selected yet,
        // so we add the first available shipping rate to match the final order total.
        $transactionAmount = (float) $quoteResponse['amount'];
        $availableRates = $quoteResponse['available_rates'] ?? [];
        if (!empty($availableRates)) {
            $firstRate = reset($availableRates);
            $transactionAmount += (float) $firstRate['price'];
        }
        $amwalTransactionData = $this->createAmwalTransaction($buttonConfig, $transactionAmount);
        $this->assertIsArray($amwalTransactionData);
        $this->assertArrayHasKey('id', $amwalTransactionData, 'Amwal Transaction did not return a transaction ID');

        $addressData['orderId'] = $amwalTransactionData['id'];

        return [$cartId, $amwalTransactionData, $addressData, $buttonConfig->getRefId()];
    }

    /**
     * @covers \Amwal\Payments\Model\Checkout\PlaceOrder::execute
     * @depends testGetQuote
     */
    public function testPlaceOrder(array $dependencies): array
    {
        [$cartId, $amwalTransactionData, $addressData, $refId] = $dependencies;

        /** /V1/amwal/place-order */
        $order = $this->placeOrder->execute(
            $addressData,
            $cartId,
            $refId,
            $this->getMockRefIdData(),
            $amwalTransactionData['id'],
            'test-case',
            true,
            '545454'
        );

        $this->assertTrue(is_a($order, OrderInterface::class));
        $this->assertEquals('pending_payment', $order->getState());
        $this->assertNotEmpty($order->getEntityId());
        $this->assertNotEmpty($order->getAmwalOrderId());
        $this->assertEquals($refId, (string) $order->getData('ref_id'));

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $persistedOrder = $orderRepository->get((int) $order->getEntityId());

        $this->assertEquals($amwalTransactionData['id'], $persistedOrder->getAmwalOrderId());
        $this->assertEquals(ConfigProvider::CODE, (string) $persistedOrder->getPayment()->getMethod());

        return [
            'order' => $order,
            'ref_id' => $refId,
            'amwal_order_id' => $amwalTransactionData['id'],
            'grand_total' => $order->getGrandTotal(),
        ];
    }

    /**
     * @covers \Amwal\Payments\Model\Checkout\PayOrder::execute
     * @depends testPlaceOrder
     *
     * @param array $dependencies
     *
     * @return void
     * @throws LocalizedException
     */
    public function testPayOrder(array $dependencies): void
    {
        $order = $dependencies['order'];
        $this->mockGetAmwalOrderData($dependencies);

        /** @var PayOrder $payOrder */
        $payOrder = $this->objectManager->create(PayOrder::class);

        /** /V1/amwal/pay-order */
        $response = $payOrder->execute(
            (int) $order->getEntityId(),
            $order->getAmwalOrderId()
        );

        $this->assertIsBool($response);
        $this->assertTrue($response);

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        /** @var Config $config */
        $config = $this->objectManager->get(Config::class);
        $updatedOrder = $orderRepository->get((int) $order->getEntityId());

        $this->assertEquals($config->getOrderConfirmedStatus(), $updatedOrder->getState());
    }

    /**
     * @covers \Amwal\Payments\Model\Settings::getSettings
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
     * @covers \Amwal\Payments\Cron\PendingOrdersUpdate::execute
     * @depends testPlaceOrder
     *
     * @return void
     * @throws JsonException
     */
    public function testPendingOrdersUpdate(array $dependencies): void
    {
        $this->mockGetAmwalOrderData($dependencies);

        /** @var PendingOrdersUpdate $pendingOrdersUpdate */
        $pendingOrdersUpdate = $this->objectManager->create(PendingOrdersUpdate::class);

        $pendingOrdersUpdate->execute();
    }


    /**
     * @covers \Amwal\Payments\Cron\CanceledOrdersUpdate::execute
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
     * @covers \Amwal\Payments\Block\Adminhtml\Order\View\Tab\AmwalTab::getTabLabel
     *
     * @return void
     */
    public function testAmwalTab(): void
    {
        /** @var AmwalTab $amwalTab */
        $amwalTab = $this->objectManager->get(AmwalTab::class);

        $this->assertIsString((string) $amwalTab->getTabLabel());
        $this->assertNotEmpty((string) $amwalTab->getTabLabel());
    }

    /**
     * @covers \Amwal\Payments\Block\Product\View\Promotion::isPromotionsActive
     *
     * @return void
     */
    public function testPromotionBlock(): void
    {
        /** @var Promotion $promotion */
        $promotion = $this->objectManager->get(Promotion::class);

        $this->assertIsBool($promotion->isPromotionsActive());
    }

    /**
     * Mock GetAmwalOrderData to return a DataObject that matches the Magento order data.
     * This is needed because the Amwal QA API GET response may not include all fields
     * (e.g. ref_id) that are required for dataValidation in OrderUpdate.
     *
     * @param array $dependencies
     * @return void
     */
    private function mockGetAmwalOrderData(array $dependencies): void
    {
        $mockData = new DataObject([
            'id' => $dependencies['amwal_order_id'],
            'ref_id' => $dependencies['ref_id'],
            'total_amount' => $dependencies['grand_total'],
            'discount' => 0,
            'status' => 'success',
        ]);

        $mock = $this->createMock(GetAmwalOrderData::class);
        $mock->method('execute')->willReturn($mockData);
        $this->objectManager->addSharedInstance($mock, GetAmwalOrderData::class);

        // Force a new OrderUpdate instance that uses the mocked GetAmwalOrderData
        $orderUpdate = $this->objectManager->create(OrderUpdate::class);
        $this->objectManager->addSharedInstance($orderUpdate, OrderUpdate::class);
    }


    /**
     * Create a new Amwal transaction for testing
     *
     * @param AmwalButtonConfigInterface $buttonConfig
     * @param float $amount
     *
     * @return array
     * @throws JsonException
     */
    private function createAmwalTransaction(AmwalButtonConfigInterface $buttonConfig, float $amount): array
    {
        $transactionData = [
            'amount' => $amount,
            'currency' => 'SAR',
            'ref_id' => $buttonConfig->getRefId(),
            'success_url' => 'https://store.amwal.tech/amwal/checkout/success',
            'fail_url' => 'https://store.amwal.tech/amwal/checkout/fail',
            'client_email' => 'test@example.com',
            'client_first_name' => 'Test',
            'client_last_name' => 'User',
            'client_phone_number' => '+966501234567',
            'address_details' => [
                'street1' => '123 Test Street',
                'city' => 'Riyadh',
                'state' => 'Riyadh Province',
                'country' => 'SA',
                'postcode' => '12345'
            ],
            'items' => [
                [
                    'name' => 'Test Product',
                    'quantity' => 1,
                    'amount' => $amount
                ]
            ]
        ];

        return $this->executeAmwalCall(
            'https://qa.amwal.dev/transactions/',
            $transactionData,
            $buttonConfig->getMerchantId(),
            'POST'
        );
    }
}
