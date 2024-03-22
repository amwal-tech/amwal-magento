<?php

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

    private const MOCK_PHONE_NUMBER = '+201234567890';
    private const MOCK_EMAIL = 'integration_test_runner@amwal.tech';

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
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->getCartButtonConfig = $this->objectManager->get(GetCartButtonConfig::class);
        $this->quoteIdMaskFactory = $this->objectManager->get(QuoteIdMaskFactory::class);
        $this->getQuote = $this->objectManager->get(GetQuote::class);
        $this->placeOrder = $this->objectManager->get(PlaceOrder::class);
        $this->payOrder = $this->objectManager->get(PayOrder::class);
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

        $transactionPhone = $this->setAmwalTransactionPhone($buttonConfig, $amwalTransactionData['id']);
        $this->assertIsArray($transactionPhone);
        $this->assertArrayHasKey('client_phone_number', $transactionPhone);
        $this->assertEquals(self::MOCK_PHONE_NUMBER, $transactionPhone['client_phone_number']);

        $transactionAddress = $this->setAmwalTransactionAddress($buttonConfig, $amwalTransactionData['id']);
        $this->assertIsArray($transactionAddress);
        $this->assertArrayHasKey('address_details', $transactionAddress);
        $this->assertIsArray($transactionAddress['address_details']);

        $addressData = [
            'street1' => $transactionAddress['address_details']['street1'],
            'country' => $transactionAddress['address_details']['country'],
            'city' => $transactionAddress['address_details']['city'],
            'state' => $transactionAddress['address_details']['state'],
            'postcode' => $transactionAddress['address_details']['postcode'],
            'client_phone_number' => $transactionAddress['client_phone_number'],
            'client_email' => $transactionAddress['client_email'],
            'client_first_name' => $transactionAddress['client_first_name'],
            'client_last_name' => $transactionAddress['client_last_name'],
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

        $transactionShipping = $this->setAmwalTransactionShipping($quoteResponse, $buttonConfig, $amwalTransactionData['id']);
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
        $this->assertEquals(self::MOCK_EMAIL, $order->getCustomerEmail());
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
     * Amwal pop-up - Generate a transaction on button press
     *
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

        return $this->executeAmwalCall(
            'https://qa-backend.sa.amwal.tech/transactions/',
            $requestData,
            $buttonConfig->getMerchantId()
        );
    }

    /**
     * Amwal pop-up - Submitting phone number
     *
     * @param AmwalButtonConfigInterface $buttonConfig
     * @param string $transactionId
     *
     * @return mixed
     * @throws JsonException
     */
    private function setAmwalTransactionPhone(AmwalButtonConfigInterface $buttonConfig, string $transactionId)
    {
        $requestData = [
            'phone_number' => self::MOCK_PHONE_NUMBER,
        ];

        return $this->executeAmwalCall(
            'https://qa-backend.sa.amwal.tech/transactions/' . $transactionId . '/phone',
            $requestData,
            $buttonConfig->getMerchantId()
        );
    }

    /**
     * Amwal pop-up - Submitting address information
     *
     * @param AmwalButtonConfigInterface $buttonConfig
     * @param string $transactionId
     *
     * @return mixed
     * @throws JsonException
     */
    private function setAmwalTransactionAddress(AmwalButtonConfigInterface $buttonConfig, string $transactionId)
    {
        $requestData = [
            'email' => self::MOCK_EMAIL,
            'first_name' => 'PHP Unit',
            'last_name' => 'Test Runner',
            'street1' => '32 Honey Bluff Road',
            'state' => 'Alaska',
            'city' => 'Arkansas',
            'country' => 'US',
            'postcode' => '29720',
            'state_code' => '2'
        ];

        return $this->executeAmwalCall(
            'https://qa-backend.sa.amwal.tech/transactions/' . $transactionId . '/address',
            $requestData,
            $buttonConfig->getMerchantId()
        );
    }

    /**
     * Amwal pop-up - Submitting shipping information
     *
     * @param array $quoteResponse
     * @param AmwalButtonConfigInterface $buttonConfig
     * @param string $transactionId
     *
     * @return mixed
     * @throws JsonException
     */
    private function setAmwalTransactionShipping(array $quoteResponse, AmwalButtonConfigInterface $buttonConfig, string $transactionId)
    {
        $requestData = [
            'shipping' => $quoteResponse['available_rates']['flatrate_flatrate']['price'],
            'shipping_details' => [
                'id' => 'flatrate_flatrate',
                'label' => $quoteResponse['available_rates']['flatrate_flatrate']['carrier_title'],
                'price' => $quoteResponse['available_rates']['flatrate_flatrate']['price']
            ],
            'taxes' => $quoteResponse['tax_amount'],
            'discount' => $quoteResponse['discount_amount'],
            'fees' => $quoteResponse['additional_fee_amount'],
            'amount' => $quoteResponse['amount']
        ];

        return $this->executeAmwalCall(
            'https://qa-backend.sa.amwal.tech/transactions/' . $transactionId . '/shipping',
            $requestData,
            $buttonConfig->getMerchantId()
        );
    }
}
