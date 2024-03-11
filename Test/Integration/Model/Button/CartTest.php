<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\Api\Model\Button;

use Amwal\Payments\Api\Data\AmwalButtonConfigInterface;
use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Api\Data\RefIdDataInterfaceFactory;
use Amwal\Payments\Model\Button\GetCartButtonConfig;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Quote\Api\GuestCartItemRepositoryInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
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
    private const EXPECTED_KEYS = [
        'merchant_id', 'amount', 'country_code', 'dark_mode', 'email_required',
        'address_required', 'address_handshake', 'ref_id', 'label', 'disabled',
        'show_payment_brands', 'enable_pre_checkout_trigger', 'enable_pre_pay_trigger',
        'id', 'test_environment', 'allowed_address_countries', 'allowed_address_states',
        'plugin_version', 'post_code_optional_countries', 'installment_options_url',
        'show_discount_ribbon', 'discount'
    ];

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var ProductResource|null
     */
    private ?ProductResource $productResource = null;

    /**
     * @var GuestCartManagementInterface|null
     */
    private ?GuestCartManagementInterface $guestCartManagement = null;

    /**
     * @var CartItemInterfaceFactory|null
     */
    private ?CartItemInterfaceFactory $cartItemFactory = null;

    /**
     * @var GuestCartItemRepositoryInterface|null
     */
    private ?GuestCartItemRepositoryInterface $guestCartItemRepository = null;

    /**
     * @var GetCartButtonConfig|null
     */
    private ?GetCartButtonConfig $getCartButtonConfig = null;


    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productResource = $this->objectManager->get(ProductResource::class);
        $this->guestCartManagement = $this->objectManager->get(GuestCartManagementInterface::class);
        $this->cartItemFactory = $this->objectManager->get(CartItemInterfaceFactory::class);
        $this->guestCartItemRepository = $this->objectManager->get(GuestCartItemRepositoryInterface::class);
        $this->getCartButtonConfig = $this->objectManager->get(GetCartButtonConfig::class);
        $this->refIdDataFactory = $this->objectManager->get(RefIdDataInterfaceFactory::class);
    }

    /**
     * Test getCartButtonConfig
     * @magentoApiDataFixture Amwal_Payments::Test/Api/_files/cart.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetCartButtonConfig()
    {
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

        /** /V1/guest-cart */
        $cartId = $this->guestCartManagement->createEmptyCart();
        $this->assertNotEmpty($cartId);

        /** @var CartItemInterface $cartItem */
        $cartItem = $this->cartItemFactory->create();
        $cartItem->addData([
            CartItemInterface::KEY_QUOTE_ID => $cartId,
            CartItemInterface::KEY_SKU => 'amwal_simple',
            CartItemInterface::KEY_QTY => 1
        ]);

        /** /V1/guest-carts/:cartId/items */
        $item = $this->guestCartItemRepository->save($cartItem);
        $this->assertNotEmpty($item);


        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load($cartId);

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

        /** @var RefIdDataInterface $refIdData */
        $refIdData = $this->refIdDataFactory->create();
        $refIdData->setData([
            RefIdDataInterface::IDENTIFIER => '100',
            RefIdDataInterface::CUSTOMER_ID => '0',
            RefIdDataInterface::TIMESTAMP => '1707916143'
        ]);

        /** /V1/amwal/button/cart */
        $response = $this->getCartButtonConfig->execute(
            $refIdData,
            'product-detail-page',
            $cartId
        );

        $this->assertTrue(is_a($response, AmwalButtonConfigInterface::class));

        $response = $response->toArray();

        // Perform assertions
        foreach (self::EXPECTED_KEYS as $key) {
            $this->assertArrayHasKey($key, $response);
        }

        // Validate specific values if needed
        $this->assertIsString($response['merchant_id']);
        $this->assertIsNumeric($response['amount']);
        $this->assertGreaterThan(0, $response['amount']);

        $tempData = [
            'cartId' => $cartId,
            'refId' => $response['ref_id'],
            'orderId' => $quote->getEntityId(),
        ];
        file_put_contents(__DIR__ . '../../../_files/GetCartData.php', "<?php\n\nreturn " . var_export($tempData, true) . ";\n");
    }
}
