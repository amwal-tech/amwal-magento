<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\Integration\Model\Checkout;

use Amwal\Payments\Model\Checkout\PayOrder;
use Amwal\Payments\Test\Integration\IntegrationTestBase;

class PayOrderTest extends IntegrationTestBase
{
    /**
     * @var PayOrder|null
     */
    private ?PayOrder $payOrder = null;

    protected function setUp(): void
    {
        $this->payOrder = $this->objectManager->get(PayOrder::class);
    }

    /**
     * @Depends PlaceOrderTest::testPlaceOrder
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
