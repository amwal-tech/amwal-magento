<?php
declare(strict_types=1);

namespace Amwal\Payments\Ui\Component\Listing\Column;

use Amwal\Payments\ViewModel\ExpressCheckoutButton;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class TriggerContextOptions
 */
class TriggerContextOptions implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => ExpressCheckoutButton::TRIGGER_CONTEXT_CART, 'label' => __('Cart')],
            ['value' => ExpressCheckoutButton::TRIGGER_CONTEXT_MINICART, 'label' => __('Mini Cart')],
            ['value' => ExpressCheckoutButton::TRIGGER_CONTEXT_PRODUCT_DETAIL, 'label' => __('Product Detail Page')],
            ['value' => ExpressCheckoutButton::TRIGGER_CONTEXT_PRODUCT_LIST, 'label' => __('Product Listing Page')],
            ['value' => ExpressCheckoutButton::TRIGGER_CONTEXT_REGULAR_CHECKOUT, 'label' => __('Regular Checkout')],
            ['value' => ExpressCheckoutButton::TRIGGER_CONTEXT_LOGIN, 'label' => __('Login')]
        ];
    }
}
