<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\Api\Model\Checkout;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Webapi\Rest\Request;

class PayOrderTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/amwal/pay-order';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Test placeOrder
     */
    public function testPayOrder()
    {
        $this->_markTestAsRestOnly();

        $serviceInfoForPayOrder = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];

        $requestData = [
            'order_id' => '13',
            'amwal_order_id' => '32574861-7bdf-414b-b65b-3736258644cd',
        ];

        $response = $this->_webApiCall($serviceInfoForPayOrder, $requestData);
        $this->assertIsBool($response);
    }
}
