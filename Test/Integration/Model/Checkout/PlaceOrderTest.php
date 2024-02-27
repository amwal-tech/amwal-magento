<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\ApiFunctional\Model\Checkout;

use Magento\Quote\Model\Quote;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Webapi\Rest\Request;

class PlaceOrderTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/amwal/place-order';

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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPlaceOrder()
    {
        $this->_markTestAsRestOnly();

        $tempData = require __DIR__ . '../../../_files/GetCartData.php';

        $serviceInfoForPlaceOrder = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ]
        ];

        $requestData = [
            'shipping' => $tempData['shipping_amount'],
            'shipping_details' => [
                'id' => 'freeshipping_freeshipping',
                'label' => $tempData['available_rates']['freeshipping_freeshipping']['carrier_title'],
                'price' => $tempData['available_rates']['freeshipping_freeshipping']['price']
            ],
            'taxes' => $tempData['tax_amount'],
            'discount' => $tempData['discount_amount'],
            'fees' => $tempData['additional_fee_amount'],
            'amount' => $tempData['amount'],
            'merchantId' => $tempData['merchantId'],
        ];
        $transactionShipping = $this->_executeCurl('https://qa-backend.sa.amwal.tech/transactions/' . $tempData['amwal_order_id'] . '/shipping', $requestData);
        $this->assertNotEmpty($transactionShipping);


        $requestData = [
            'order_id' => $tempData['amwal_order_id'],
            'order_entity_id' => $tempData['orderId'],
            'order_created_at' => '2024-02-11 18:57:56',
            'order_content' => json_encode([
                [
                    'id' => '1',
                    'name' => 'Simple Product',
                    'quantity' => '1',
                    'total' => '10.00',
                    'url' => 'https://store.amwal.tech/simple-product.html',
                    'image' => 'https://store.amwal.tech/media/catalog/product/1/0/10-1.png'
                ]
            ]),
            'order_position' => 'product-detail-page',
            'plugin_type' => 'magento',
            'plugin_version' => '1.0.32',
            'merchantId' => $tempData['merchantId'],
        ];
        $transactionOrderDetails = $this->_executeCurl('https://qa-backend.sa.amwal.tech/transactions/' . $tempData['amwal_order_id'] . '/set_order_details', $requestData);
        $this->assertNotEmpty($transactionOrderDetails);

        $requestData = [
            'ref_id' => $tempData['refId'],
            'address_data' => [
                'address_details' => [
                    'city' => 'Giza',
                    'state' => 'EG',
                    'country' => 'SA',
                    'street1' => '192 Nasr El Din, Haram, Giza, 12511',
                    'postcode' => '12511'
                ],
                'refunded_amount' => '0.00',
                'amount' => $tempData['amount'],
                'amwal_fee' => null,
                'amwal_tax_vat' => null,
                'app_notified' => false,
                'approval_type' => null,
                'brand_percentage' => null,
                'brand_static_fee' => null,
                'card_last_4_digits' => '',
                'card_payment_brand' => '',
                'client_email' => 'mardi@amwal.tech',
                'client_first_name' => 'Ahmed',
                'client_last_name' => 'Mardi',
                'client_phone_number' => '+201033233462',
                'client_registered' => true,
                'created_at' => '2024-02-11T18:57:56.165492+03:00',
                'discount' => $tempData['discount_amount'],
                'fees' => $tempData['additional_fee_amount'],
                'has_new_registration' => false,
                'hyperpay_checkout_id' => null,
                'hypersplit_id' => null,
                'id' => $tempData['amwal_order_id'],
                'is_approved_by_client' => null,
                'is_refundable' => false,
                'merchant_business_name' => 'Zerox',
                'merchant_country_code' => 'SA',
                'merchant_english_business_name' => null,
                'merchant_key' => $tempData['merchantId'],
                'merchant_payout' => null,
                'paymentBrand' => null,
                'payment_method' => null,
                'ref_id' => $tempData['refId'],
                'shipping' => $tempData['shipping_amount'],
                'shipping_details' => [
                    'id' => 'freeshipping_freeshipping',
                    'label' => $tempData['available_rates']['freeshipping_freeshipping']['carrier_title'],
                    'price' => $tempData['available_rates']['freeshipping_freeshipping']['price']
                ],
                'status' => 'success',
                'store_domain' => 'https://woo.amwal.dev',
                'store_logo' => 'https://qa-backend.sa.amwal.tech/media/store_logo/download.png',
                'taxes' => $tempData['tax_amount'],
                'total_amount' => $tempData['amount'],
                'transaction_status' => null,
                'failure_reason' => null,
                'type' => 'SANDBOX',
                'gateway' => null,
                'gateway_type' => 1,
                'card_bin' => '400000'
            ],
            'cartId' => $tempData['cartId'],
            'amwal_order_id' => $tempData['amwal_order_id'],
            'ref_id_data' => [
                'identifier' => '100',
                'customer_id' => '0',
                'timestamp' => '1707916143'
            ],
            'trigger_context' => 'product-detail-page',
            'has_amwal_address' => true
        ];
        $response = $this->_webApiCall($serviceInfoForPlaceOrder, $requestData);
        $this->assertIsArray($response);


        // Perform assertions
        $this->assertEquals('pending_payment', $response['state']);
        $this->assertArrayHasKey('entity_id', $response);

        $newTempData = [
            'orderId' => $response['entity_id'],
        ];
        $tempData = array_merge($tempData, $newTempData);
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
