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
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;

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
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->getCartButtonConfig = $this->objectManager->get(GetCartButtonConfig::class);
        $this->getQuote = $this->objectManager->get(GetQuote::class);
        $this->placeOrder = $this->objectManager->get(PlaceOrder::class);
        $this->payOrder = $this->objectManager->get(PayOrder::class);
    }

    /**
     * @magentoDataFixture Amwal_Payments::Test/Integration/_files/simple_product.php
     * @covers GetCartButtonConfig::execute
     *
     * @return AmwalButtonConfigInterface
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testGetCartButtonConfig(): AmwalButtonConfigInterface
    {
        /** /V1/guest-cart */
        $cartId = $this->createGuestCart();
        $this->assertNotEmpty($cartId);

        /** /V1/guest-carts/:cartId/items */
        $item = $this->addSampleProductToCart();
        $this->assertNotEmpty($item);

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

        return $buttonConfig;
    }

    /**
     * @coversNothing
     * @depends testGetCartButtonConfig
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @param AmwalButtonConfigInterface $buttonConfig
     *
     * @return array
     */
    public function testCreateAmwalTransaction(AmwalButtonConfigInterface $buttonConfig): array
    {
        $requestData = [
            'merchantId' => $buttonConfig->getMerchantId(),
            'amount' => $buttonConfig->getAmount(),
            'taxes' => 0,
            'discount' => $buttonConfig->getDiscount(),
            'fees' => 0,
            'client_email' => 'integration.test@amwal.tech',
            'client_first_name' => 'Integration',
            'client_last_name' => 'Tester',
            'client_phone_number' => '+201234567890',
            'order_details' => [
                'order_position' => 'product-detail-page',
                'plugin_version' => 'integration-test'
            ],
            'address_details' => [
                'city' => 'Cairo',
                'state' => 'Cairo',
                'postcode' => '4472001',
                'country' => 'EG',
                'street1' => 'El-Thawra Street Sheraton Al Matar',
                'street2' => '',
                'email' => 'integration.test@amwal.tech'
            ],
            'refId' => $buttonConfig->getRefId(),
            'uniqueRef' => false
        ];

        $transactionData = $this->executeCurl('https://qa-backend.sa.amwal.tech/transactions/', $requestData);
        $this->assertNotEmpty($transactionData);

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
            'orderId' => $transactionData['id'],
        ];

        return [$buttonConfig, $addressData, $transactionData];
    }

    /**
     * @covers  GetQuote::execute
     * @depends testCreateAmwalTransaction
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @param array $dependencies
     *
     * @return array
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testGetQuote(array $dependencies): array
    {
        [$buttonConfig, $addressData, $transactionData] = $dependencies;

        /** /V1/amwal/get-quote */
        $quoteResponse = $this->getQuote->execute(
            [],
            $buttonConfig->getRefId(),
            $this->getMockRefIdData(),
            $addressData,
            false,
            $this->getMaskedGuestCartId()
        );

        $this->assertIsArray($quoteResponse);

        // Perform assertions
        foreach (self::GET_QUOTE_EXPECTED_KEYS as $key) {
            $this->assertArrayHasKey($key, $quoteResponse);
        }

        // Validate specific values if needed
        $this->assertIsNumeric($quoteResponse['amount']);
        $this->assertGreaterThan(0, $quoteResponse['amount']);

        $this->assertIsNumeric($quoteResponse['subtotal']);
        $this->assertGreaterThan(0, $quoteResponse['subtotal']);

        return [$buttonConfig, $quoteResponse, $transactionData];
    }

    /**
     * @covers PlaceOrder::execute
     * @depends testGetQuote
     */
    public function testPlaceOrder(array $dependencies): OrderInterface
    {
        [$buttonConfig, $quoteResponse, $transactionData] = $dependencies;

        $requestData = [
            'shipping' => $quoteResponse['shipping_amount'],
            'shipping_details' => [
                'id' => 'freeshipping_freeshipping',
                'label' => $quoteResponse['available_rates']['freeshipping_freeshipping']['carrier_title'],
                'price' => $quoteResponse['available_rates']['freeshipping_freeshipping']['price']
            ],
            'taxes' => $quoteResponse['tax_amount'],
            'discount' => $quoteResponse['discount_amount'],
            'fees' => $quoteResponse['additional_fee_amount'],
            'amount' => $quoteResponse['amount'],
            'merchantId' => $buttonConfig['merchantId'],
        ];
        $transactionShipping = $this->executeCurl(
            'https://qa-backend.sa.amwal.tech/transactions/' . $transactionData['id'] . '/shipping',
            $requestData
        );

        $this->assertNotEmpty($transactionShipping);

        /** /V1/amwal/place-order */
        $order = $this->placeOrder->execute(
            $this->getGuestCartId(),
            $buttonConfig->getRefId(),
            $this->getMockRefIdData(),
            $transactionData['id'],
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
     * @return void
     * @throws LocalizedException
     */
    public function testPayOrder(): void
    {
        $order = $this->getOrderResponse();

        /** @var /V1/amwal/pay-order $response */
        $response = $this->payOrder->execute(
            $order->getEntityId(),
            $order->getAmwalOrderId()
        );

        $this->assertIsBool($response);
        $this->assertTrue($response);
    }
}
