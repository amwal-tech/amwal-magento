<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\Integration\Model\Checkout;

use Amwal\Payments\Model\Checkout\PlaceOrder;
use Amwal\Payments\Test\Integration\IntegrationTestBase;
use Magento\Sales\Api\Data\OrderInterface;

class PlaceOrderTest extends IntegrationTestBase
{
    /**
     * @var PlaceOrder|null
     */
    private ?PlaceOrder $placeOrder = null;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->placeOrder = $this->objectManager->get(PlaceOrder::class);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPlaceOrder(): void
    {
        $buttonConfig = $this->getCartButtonConfigResponse();
        $quoteResponse = $this->getQuoteResponse();
        $amwalTransactionData = $this->getAmwalTransactionData();

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
            'https://qa-backend.sa.amwal.tech/transactions/' . $amwalTransactionData['id'] . '/shipping',
            $requestData
        );
        $this->assertNotEmpty($transactionShipping);

        /** /V1/amwal/place-order */
        $order = $this->placeOrder->execute(
            $this->getGuestCartId(),
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

        $this->setOrderResponse($order);
    }
}
