<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\ApiFunctional\Model\Checkout;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Webapi\Rest\Request;

class PayOrderTest extends WebapiAbstract
{
    private const SERVICE_VERSION = 'V1';
    private const SERVICE_NAME = 'Amwal';
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
        $tempData = require __DIR__ . '../../../_files/TempData.php';

        $serviceInfoForPayOrder = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'PayOrder',
            ],
        ];

        $requestData = [
            'order_id' => $tempData['orderId'],
            'amwal_order_id' => $tempData['amwal_order_id'],
        ];
        $response = $this->_webApiCall($serviceInfoForPayOrder, $requestData);
        $this->assertIsBool($response);
    }
}
