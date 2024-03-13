<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\Integration\Model\Checkout;

use Amwal\Payments\Model\Button\GetCartButtonConfig;
use Amwal\Payments\Model\Checkout\GetQuote;
use Amwal\Payments\Test\Integration\IntegrationTestBase;

class GetQuoteTest extends IntegrationTestBase
{
    private const EXPECTED_KEYS = [
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
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->getCartButtonConfig = $this->objectManager->get(GetCartButtonConfig::class);
        $this->getQuote = $this->objectManager->get(GetQuote::class);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetQuote(): void
    {
        // Use button config response from dependent test
        $buttonConfig = $this->getCartButtonConfigResponse();
        $this->assertNotEmpty($buttonConfig);

        $requestData = [
            'merchantId' => $buttonConfig->getMerchantId(),
            'amount' => $buttonConfig->getAmount(),
            'taxes' => 0,
            'discount' => $buttonConfig->getDiscount(),
            'fees' => 0,
            'client_email' => 'mardi@amwal.tech',
            'client_first_name' => 'Ahmed',
            'client_last_name' => 'Mardi',
            'client_phone_number' => '+201033233462',
            'order_details' => [
                'order_position' => 'product-detail-page',
                'plugin_version' => 'Magento 1.0.32'
            ],
            'address_details' => [
                'city' => 'Cairo',
                'state' => 'Cairo',
                'postcode' => '4472001',
                'country' => 'EG',
                'street1' => 'El-Thawra Street Sheraton Al Matar',
                'street2' => '',
                'email' => 'mardi@amwal.tech'
            ],
            'refId' => $buttonConfig->getRefId(),
            'uniqueRef' => false
        ];

        $transactionData = $this->executeCurl('https://qa-backend.sa.amwal.tech/transactions/', $requestData);
        $this->assertNotEmpty($transactionData);

        $addressData = [
            'id' => '6e369835-451c-4071-8d86-496bd4a19eb6',
            'street1' => '192 Nasr El Din, Haram, Giza, 12511',
            'country' => 'SA',
            'city' => 'Giza',
            'state' => 'EG',
            'postcode' => '12511',
            'client_phone_number' => '+201033233462',
            'client_email' => 'mardi@amwal.tech',
            'client_first_name' => 'Ahmed',
            'client_last_name' => 'Mardi',
            'orderId' => $transactionData['id'],
        ];

        /** /V1/amwal/get-quote */
        $response = $this->getQuote->execute(
            [],
            $buttonConfig->getRefId(),
            $this->getMockRefIdData(),
            $addressData,
            false,
            $this->getMaskedGuestCartId()
        );

        $this->assertIsArray($response);

        // Perform assertions
        foreach (self::EXPECTED_KEYS as $key) {
            $this->assertArrayHasKey($key, $response);
        }

        // Validate specific values if needed
        $this->assertIsNumeric($response['amount']);
        $this->assertGreaterThan(0, $response['amount']);

        $this->assertIsNumeric($response['subtotal']);
        $this->assertGreaterThan(0, $response['subtotal']);

        $this->setQuoteResponse($response);
        $this->setAmwalTransactionData($transactionData);
    }
}
