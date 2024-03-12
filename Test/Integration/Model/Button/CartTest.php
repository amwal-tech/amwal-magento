<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\Api\Model\Button;

use Amwal\Payments\Api\Data\AmwalButtonConfigInterface;
use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Api\Data\RefIdDataInterfaceFactory;
use Amwal\Payments\Model\Button\GetCartButtonConfig;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Quote\Api\GuestCartItemRepositoryInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    private const EXPECTED_KEYS = [
        'merchant_id', 'amount', 'country_code', 'dark_mode', 'email_required',
        'address_required', 'address_handshake', 'ref_id', 'label', 'disabled',
        'show_payment_brands', 'enable_pre_checkout_trigger', 'enable_pre_pay_trigger',
        'id', 'test_environment', 'allowed_address_countries', 'allowed_address_states',
        'plugin_version', 'post_code_optional_countries', 'installment_options_url',
        'show_discount_ribbon', 'discount'
    ];

    public const TEST_PRODUCT_SKU = 'amwal_simple';

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

    /**
     * @var RefIdDataInterfaceFactory|null
     */
    private ?RefIdDataInterfaceFactory $refIdDataFactory = null;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productResource = $objectManager->get(ProductResource::class);
        $this->guestCartManagement = $objectManager->get(GuestCartManagementInterface::class);
        $this->cartItemFactory = $objectManager->get(CartItemInterfaceFactory::class);
        $this->cartRepository = $objectManager->get(CartRepositoryInterface::class);
        $this->guestCartItemRepository = $objectManager->get(GuestCartItemRepositoryInterface::class);
        $this->getCartButtonConfig = $objectManager->get(GetCartButtonConfig::class);
        $this->refIdDataFactory = $objectManager->get(RefIdDataInterfaceFactory::class);
    }

    /**
     * Test getCartButtonConfig
     * @magentoDataFixture Amwal_Payments::Test/Integration/_files/cart.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetCartButtonConfig()
    {
        /** /V1/guest-cart */
        $cartId = $this->guestCartManagement->createEmptyCart();
        $this->assertNotEmpty($cartId);

        /** @var CartItemInterface $cartItem */
        $cartItem = $this->cartItemFactory->create();
        $cartItem->addData([
            CartItemInterface::KEY_QUOTE_ID => $cartId,
            CartItemInterface::KEY_SKU => self::TEST_PRODUCT_SKU,
            CartItemInterface::KEY_QTY => 1
        ]);

        /** /V1/guest-carts/:cartId/items */
        $item = $this->guestCartItemRepository->save($cartItem);
        $this->assertNotEmpty($item);


        /** @var Quote $quote */
        $quote = $this->cartRepository->get($cartId);

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
