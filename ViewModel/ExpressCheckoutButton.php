<?php
declare(strict_types=1);

namespace Amwal\Payments\ViewModel;

use Amwal\Payments\Api\Data\AmwalAddressInterfaceFactory;
use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Api\Data\RefIdDataInterfaceFactory;
use Amwal\Payments\Api\RefIdManagementInterface;
use Amwal\Payments\Model\Config as AmwalConfig;
use Amwal\Payments\Model\ThirdParty\CityHelper;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Session;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Locale\Resolver as LocaleResolver;

class ExpressCheckoutButton implements ArgumentInterface
{
    public const TRIGGER_CONTEXT_PRODUCT_LIST = 'product-listing-page';
    public const TRIGGER_CONTEXT_PRODUCT_DETAIL = 'product-detail-page';
    public const TRIGGER_CONTEXT_MINICART = 'minicart';
    public const TRIGGER_CONTEXT_CART = 'cart';

    public const CHECKOUT_BUTTON_ID_PREFIX = 'amwal-checkout-button-';

    /**
     * @var AmwalConfig
     */
    protected AmwalConfig $config;

    /**
     * @var Random
     */
    private Random $random;

    /**
     * @param AmwalConfig $config
     * @param Random $random
     */
    public function __construct(
        AmwalConfig $config,
        Random $random
    ) {
        $this->config = $config;
        $this->random = $random;
    }

    /**
     * @param string $triggerContext
     * @return bool
     */
    public function shouldRender(string $triggerContext): bool
    {
        return $this->isExpressCheckoutActive();
    }

    /**
     * @return bool
     */
    public function isExpressCheckoutActive(): bool
    {
        return !(!$this->config->isActive() || !$this->config->isExpressCheckoutActive());
    }

    /**
     * @return bool
     */
    public function shouldHideProceedToCheckout(): bool
    {
        return $this->config->shouldHideProceedToCheckout();
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getUniqueId(): string
    {
        return self::CHECKOUT_BUTTON_ID_PREFIX . '-' . $this->random->getRandomString(8);
    }

    /**
     * @param string|null $formSelector
     * @param ProductInterface|null $product
     * @return string
     */
    public function getFormSelector(?string $formSelector, ?ProductInterface $product): string
    {
        if (!$formSelector) {
            return '';
        }

        if (strpos($formSelector, '%product_id%') && $product) {
            $formSelector = str_replace('%product_id%', (string) $product->getId(), $formSelector);
        }

        return $formSelector;
    }
}
