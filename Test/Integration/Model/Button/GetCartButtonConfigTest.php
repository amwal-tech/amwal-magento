<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\Integration\Model\Button;

use Amwal\Payments\Api\Data\AmwalButtonConfigInterface;
use Amwal\Payments\Api\Data\RefIdDataInterfaceFactory;
use Amwal\Payments\Model\Button\GetCartButtonConfig;
use Amwal\Payments\Test\Integration\IntegrationTestBase;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;

class GetCartButtonConfigTest extends IntegrationTestBase
{
    private const EXPECTED_KEYS = [
        'merchant_id', 'amount', 'country_code', 'dark_mode', 'email_required',
        'address_required', 'address_handshake', 'ref_id', 'label', 'disabled',
        'show_payment_brands', 'enable_pre_checkout_trigger', 'enable_pre_pay_trigger',
        'id', 'test_environment', 'allowed_address_countries', 'allowed_address_states',
        'plugin_version', 'postcode_optional_countries', 'installment_options_url',
        'show_discount_ribbon', 'discount'
    ];

    /**
     * @var GetCartButtonConfig|null
     */
    private ?GetCartButtonConfig $getCartButtonConfig = null;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->getCartButtonConfig = $this->objectManager->get(GetCartButtonConfig::class);
    }

    /**
     * @magentoDataFixture Amwal_Payments::Test/Integration/_files/simple_product.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetCartButtonConfig(): void
    {
        /** /V1/guest-cart */
        $cartId = $this->createGuestCart();
        $this->assertNotEmpty($cartId);

        /** /V1/guest-carts/:cartId/items */
        $item = $this->addSampleProductToCart();
        $this->assertNotEmpty($item);

        $refIdData = $this->getMockRefIdData();

        /** /V1/amwal/button/cart */
        $cartButtonConfig = $this->getCartButtonConfig->execute(
            $refIdData,
            'product-detail-page',
            $cartId
        );

        $this->assertTrue(is_a($cartButtonConfig, AmwalButtonConfigInterface::class));

        $response = $cartButtonConfig->toArray();

        // Perform assertions
        foreach (self::EXPECTED_KEYS as $key) {
            $this->assertArrayHasKey($key, $response);
        }

        // Validate specific values if needed
        $this->assertIsString($response['merchant_id']);
        $this->assertIsNumeric($response['amount']);
        $this->assertGreaterThan(0, $response['amount']);

        $this->setCartButtonConfigResponse($cartButtonConfig);
    }
}
