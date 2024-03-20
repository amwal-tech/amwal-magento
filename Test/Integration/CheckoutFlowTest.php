<?php
/**
 * Copyright © Youwe. All rights reserved.
 * https://www.youweagency.com
 */

declare(strict_types=1);

namespace Amwal\Payments\Test\Integration;

use Amwal\Payments\Api\Data\AmwalButtonConfigInterface;
use Amwal\Payments\Model\Button\GetCartButtonConfig;
use Amwal\Payments\Model\Checkout\GetQuote;
use Amwal\Payments\Model\Checkout\PayOrder;
use Amwal\Payments\Model\Checkout\PlaceOrder;
use Exception;
use JsonException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Sales\Api\Data\OrderInterface;
use TddWizard\Fixtures\Catalog\ProductBuilder;
use TddWizard\Fixtures\Catalog\ProductFixture;
use TddWizard\Fixtures\Checkout\CartBuilder;

/**
 * Tests the full checkout flow consisting of
 *    - Retrieving button configuration
 *    - Retrieving quote
 *    - Placing Order
 *    - Paying Order
 */
class CheckoutFlowTest extends IntegrationTestBase
{
    private const BUTTON_CONFIG_EXPECTED_KEYS = [
        'merchant_id', 'amount', 'country_code', 'dark_mode', 'email_required',
        'address_required', 'address_handshake', 'ref_id', 'label', 'disabled',
        'show_payment_brands', 'enable_pre_checkout_trigger', 'enable_pre_pay_trigger',
        'id', 'test_environment', 'allowed_address_countries', 'allowed_address_states',
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
     * @var PlaceOrder|null
     */
    private ?PlaceOrder $placeOrder = null;

    /**
     * @var PayOrder|null
     */
    private ?PayOrder $payOrder = null;

    /**
     * @var ProductFixture|null
     */
    private ?ProductFixture $productFixture = null;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->setupFixtures();
        $this->getCartButtonConfig = $this->objectManager->get(GetCartButtonConfig::class);
        $this->quoteIdMaskFactory = $this->objectManager->get(QuoteIdMaskFactory::class);
        $this->getQuote = $this->objectManager->get(GetQuote::class);
        $this->placeOrder = $this->objectManager->get(PlaceOrder::class);
        $this->payOrder = $this->objectManager->get(PayOrder::class);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->productFixture->rollback();
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
                $this->productFixture->getSku()
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
        echo "merchantId: " . $buttonConfig->getMerchantId() . "\n";
        print_r($amwalTransactionData);

        $addressData = [
            'id' => 'integration-test-address-id',
            'street1' => '192 Nasr El Din, Haram, Giza, 12511',
            'country' => 'SA',
            'city' => 'Giza',
            'state' => 'EG',
            'postcode' => '12511',
            'client_phone_number' => '+201234567890',
            'client_email' => 'integration.test@amwal.tech',
            'client_first_name' => 'Integration',
            'client_last_name' => 'Tester',
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

        return [$buttonConfig, $quoteResponse, $cartId, $amwalTransactionData];
    }

    /**
     * @covers PlaceOrder::execute
     * @depends testGetQuote
     */
    public function testPlaceOrder(array $dependencies): OrderInterface
    {
        /** @var AmwalButtonConfigInterface $buttonConfig */
        [$buttonConfig, $quoteResponse, $cartId, $amwalTransactionData] = $dependencies;

        $requestData = [
            'shipping' => $quoteResponse['shipping_amount'],
            'shipping_details' => [
                'id' => 'flatrate_flatrate',
                'label' => $quoteResponse['available_rates']['flatrate_flatrate']['carrier_title'],
                'price' => $quoteResponse['available_rates']['flatrate_flatrate']['price']
            ],
            'taxes' => $quoteResponse['tax_amount'],
            'discount' => $quoteResponse['discount_amount'],
            'fees' => $quoteResponse['additional_fee_amount'],
            'amount' => $quoteResponse['amount'],
            'merchantId' => $buttonConfig->getMerchantId(),
        ];
        $transactionShipping = $this->executeAmwalCall(
            'https://qa-backend.sa.amwal.tech/transactions/' . $amwalTransactionData['id'] . '/shipping/',
            $requestData
        );

        $this->assertNotEmpty($transactionShipping);

        /** /V1/amwal/place-order */
        $order = $this->placeOrder->execute(
            $cartId,
            $buttonConfig->getRefId(),
            $this->getMockRefIdData(),
            $amwalTransactionData['id'],
            'test-case',
            true
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
            $order->getEntityId(),
            $order->getAmwalOrderId()
        );

        $this->assertIsBool($response);
        $this->assertTrue($response);
    }

    /**
     * @return void
     * @throws Exception
     */
    private function setupFixtures(): void
    {
        $this->productFixture = new ProductFixture(
            ProductBuilder::aSimpleProduct()
                ->withPrice(10)
                ->build()
        );
    }

    /**
     * @param AmwalButtonConfigInterface $buttonConfig
     *
     * @return array
     * @throws JsonException
     */
    private function getAmwalTransaction(AmwalButtonConfigInterface $buttonConfig): array
    {
        $requestData = [
            'merchantID' => $buttonConfig->getMerchantId(),
            'amount' => $buttonConfig->getAmount(),
            'taxes' => 0,
            'discount' => $buttonConfig->getDiscount(),
            'fees' => 0,
            'installmentOptionsUrl' => $buttonConfig->getInstallmentOptionsUrl(),
            'order_details' => [
                'order_position' => 'PHP Unit',
                'plugin_version' => 'Integration Test Run'
            ],
            'refId' => $buttonConfig->getRefId(),
            'uniqueRef' => false
        ];

        return $this->executeAmwalCall('https://qa-backend.sa.amwal.tech/transactions/', $requestData);
    }
}
