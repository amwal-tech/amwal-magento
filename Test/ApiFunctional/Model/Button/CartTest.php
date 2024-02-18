<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\ApiFunctional\Model\Button;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Quote\Model\Quote;

class CartTest extends WebapiAbstract
{
    private const SERVICE_VERSION = 'V1';
    private const SERVICE_NAME = 'Amwal';
    private const RESOURCE_PATH = '/V1/amwal/button/cart';
    private const ADDRESS_DATA = [
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
    ];


    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var ProductResource|mixed
     */
    private mixed $productResource;


    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productResource = $this->objectManager->get(ProductResource::class);
    }

    /**
     * Test getCartButtonConfig
     * @magentoApiDataFixture Amwal_Payments::Test/ApiFunctional/_files/cart.php
     */
    public function testGetCartButtonConfig()
    {
        $this->_markTestAsRestOnly();

        $productId = $this->productResource->getIdBySku('simple_with_custom_options');
        $product = $this->objectManager->create(Product::class)->load($productId);
        $customOptionCollection = $this->objectManager->get(Option::class)
            ->getProductOptionCollection($product);
        $customOptions = [];
        foreach ($customOptionCollection as $option) {
            $customOptions [] = [
                'option_id' => $option->getId(),
                'option_value' => $option->getType() !== 'field'
                    ? current($option->getValues())->getOptionTypeId()
                    : 'test'
            ];
        }

        // Creating empty cart
        $serviceInfoForCreatingEmptyCart = [
            'rest' => [
                'resourcePath' => '/V1/guest-carts/',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => 'quoteGuestCartManagementV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'quoteGuestCartManagementV1CreateEmptyCart',
            ],
        ];
        $cartId = $this->_webApiCall($serviceInfoForCreatingEmptyCart);
        $this->assertNotEmpty($cartId);

        // Adding item to the cart
        $serviceInfoForAddingProduct = [
            'rest' => [
                'resourcePath' => '/V1/guest-carts/' . $cartId . '/items',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => 'quoteGuestCartItemRepositoryV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'quoteGuestCartItemRepositoryV1Save',
            ],
        ];

        $requestData = [
            'cartItem' => [
                'quote_id' => $cartId,
                'sku' => 'amwal_simple',
                'qty' => 1
            ]
        ];
        $item = $this->_webApiCall($serviceInfoForAddingProduct, $requestData);
        $this->assertNotEmpty($item);


        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load($item['quote_id']);

        $this->assertNotEmpty($quote->getId());

        $serviceInfoForGetCartButtonConfig = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetCartButtonConfig',
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
            'cart_id' => $cartId,
        ];

        $response = $this->_webApiCall($serviceInfoForGetCartButtonConfig, $requestData);
        $this->assertIsArray($response);

        // Perform assertions
        $this->assertArrayHasKey('merchant_id', $response);
        $this->assertArrayHasKey('amount', $response);
        $this->assertArrayHasKey('country_code', $response);
        $this->assertArrayHasKey('dark_mode', $response);
        $this->assertArrayHasKey('email_required', $response);
        $this->assertArrayHasKey('address_required', $response);
        $this->assertArrayHasKey('address_handshake', $response);
        $this->assertArrayHasKey('ref_id', $response);
        $this->assertArrayHasKey('label', $response);
        $this->assertArrayHasKey('disabled', $response);
        $this->assertArrayHasKey('show_payment_brands', $response);
        $this->assertArrayHasKey('enable_pre_checkout_trigger', $response);
        $this->assertArrayHasKey('enable_pre_pay_trigger', $response);
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('test_environment', $response);
        $this->assertArrayHasKey('allowed_address_countries', $response);
        $this->assertArrayHasKey('allowed_address_states', $response);
        $this->assertArrayHasKey('plugin_version', $response);
        $this->assertArrayHasKey('post_code_optional_countries', $response);
        $this->assertArrayHasKey('installment_options_url', $response);
        $this->assertArrayHasKey('show_discount_ribbon', $response);
        $this->assertArrayHasKey('discount', $response);

        // Validate specific values if needed
        $this->assertTrue(is_string($response['merchant_id']));
        $this->assertTrue(is_numeric($response['amount']));
        $this->assertGreaterThan(0, $response['amount']);

        $tempData = [
            'cartId' => $cartId,
            'refId' => $response['ref_id'],
            'orderId' => $quote->getEntityId(),
        ];
        file_put_contents(__DIR__ . '../../../_files/TempData.php', "<?php\n\nreturn " . var_export($tempData, true) . ";\n");
    }
}
