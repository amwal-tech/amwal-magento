<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\ApiFunctional\Model\Checkout;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Webapi\Rest\Request;

class GetQuoteTest extends WebapiAbstract
{
    private const SERVICE_VERSION = 'V1';
    private const SERVICE_NAME = 'Amwal';
    private const RESOURCE_PATH = '/V1/amwal/get-quote';
    private const EXPECTED_KEYS = [
        'cart_id', 'available_rates', 'amount', 'subtotal', 'tax_amount', 'shipping_amount',
        'discount_amount', 'additional_fee_amount', 'additional_fee_description'
    ];

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;


    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Test getQuote
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetQuote()
    {
        $tempData = require __DIR__ . '../../../_files/GetCartData.php';

        $serviceInfoForAmwalCart = [
            'rest' => [
                'resourcePath' => '/V1/amwal/button/cart',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'AmwalGetCartButtonConfig',
            ],
        ];

        $requestData = [
            'refIdData' => [
                'identifier' => '100',
                'customer_id' => '0',
                'timestamp' => '1707916143'
            ],
            'triggerContext' => 'product-detail-page',
            'locale' => 'en',
            'cartId' => $tempData['cartId'],
        ];

        $cartData = $this->_webApiCall($serviceInfoForAmwalCart, $requestData);
        $this->assertNotEmpty($cartData);

        $requestData = [
            'merchantId' => $cartData['merchant_id'],
            'amount' => $cartData['amount'],
            'taxes' => 0,
            'discount' => $cartData['discount'],
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
            'refId' => $tempData['refId'],
            'uniqueRef' => false
        ];

        $transactionData = $this->_executeCurl('https://qa-backend.sa.amwal.tech/transactions/', $requestData);
        $this->assertNotEmpty($transactionData);

        $serviceInfoForGetQuote = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetQuote',
            ],
        ];

        $requestData = [
            'ref_id' => $tempData['refId'],
            'address_data' => [
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
            ],
            'is_pre_checkout' => false,
            'trigger_context' => 'product-detail-page',
            'ref_id_data' => [
                'identifier' => '100',
                'customer_id' => '0',
                'timestamp' => '1707916143',
            ],
            'order_items' => [],
            'cartId' => $tempData['cartId'],
        ];

        $response = $this->_webApiCall($serviceInfoForGetQuote, $requestData);
        $this->assertIsArray($response);
        $response = reset($response);

        // Perform assertions
        foreach (self::EXPECTED_KEYS as $key) {
            $this->assertArrayHasKey($key, $response);
        }

        // Validate specific values if needed
        $this->assertTrue(is_numeric($response['amount']));
        $this->assertGreaterThan(0, $response['amount']);

        $this->assertTrue(is_numeric($response['subtotal']));
        $this->assertGreaterThan(0, $response['subtotal']);

        $tempData = [
            'cartId' => $tempData['cartId'],
            'refId' => $tempData['refId'],
            'orderId' => $tempData['orderId'],
            'amwal_order_id' => $transactionData['id'],
            'merchantId' => $cartData['merchant_id'],
            'available_rates' => $response['available_rates'],
            'amount' => $response['amount'],
            'subtotal' => $response['subtotal'],
            'tax_amount' => $response['tax_amount'],
            'shipping_amount' => $response['shipping_amount'],
            'discount_amount' => $response['discount_amount'],
            'additional_fee_amount' => $response['additional_fee_amount'],
            'additional_fee_description' => $response['additional_fee_description'],
        ];
        file_put_contents(__DIR__ . '../../../_files/GetCartData.php', "<?php\n\nreturn " . var_export($tempData, true) . ";\n");
    }


    /**
     * @param $url
     * @param $data
     * @param string $method
     * @return mixed
     */
    private function _executeCurl($url, $data, $method = 'POST')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'authority: qa-backend.sa.amwal.tech',
            'amwal: ' . $data['merchantId'],
            'origin: https://store.amwal.tech',
            'referer: https://store.amwal.tech',
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }
}
