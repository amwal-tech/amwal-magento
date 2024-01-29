<?php

namespace Amwal\Payments\Block\Widget;

use Amwal\Payments\Model\Config;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\Math\Random;
use Magento\Catalog\Block\Product\View\Options as ProductOptionsBlock;

class ExpressCheckoutButton extends ListProduct implements BlockInterface
{

    /**
     * @var Config
     */
    private Config $config;
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepositoryInterface;

    /**
     * @var Random
     */
    protected Random $random;

    public const CHECKOUT_BUTTON_ID_PREFIX = 'amwal-checkout-button-';

    public function __construct(
        Context                     $context,
        PostHelper                  $postDataHelper,
        Resolver                    $layerResolver,
        ProductRepositoryInterface  $productRepositoryInterface,
        CategoryRepositoryInterface $categoryRepository,
        Config                      $config,
        Random                      $random,
        StoreManagerInterface       $storeManager,
        Data                        $urlHelper, array $data = []
    )
    {
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->random = $random;
        $this->config = $config;
        $this->storeManager = $storeManager;
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
        $this->setTemplate("Amwal_Payments::express/widget-checkout-button.phtml");
    }

    public function getProduct()
    {
        $productId = $this->getData('product_id');
        if ($productId) {
            $productId = str_replace('product/', '', $productId);
        }
        $product = $this->productRepositoryInterface->getById($productId);
        return $product;
    }

    /**
     * @return string
     */
    public function getTriggerContext(): string
    {
        return 'amwal-widget';
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->config->getLocale();
    }

    /**
     * @return string
     */
    public function getStoreCode(): string
    {
        try {
            return $this->storeManager->getStore()->getCode();
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * @param int $length
     * @return string
     * @throws LocalizedException
     */
    public function getUniqueId(int $length = 8): string
    {
        return self::CHECKOUT_BUTTON_ID_PREFIX . '-' . $this->random->getRandomString($length);
    }

    /**
     * @return bool
     */
    private function shouldRender(): bool
    {
        if (!$this->config->isActive() || !$this->config->isExpressCheckoutActive()) {
            return false;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->shouldRender()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * @return float
     */
    public function getProductDiscount(): float
    {
        $product = $this->getProduct();
        $discountAmount = 0;
        if ($product) {
            $discountAmount = $this->getDiscountAmount($product);
            return $discountAmount;
        }
        return $discountAmount;
    }

    /**
     * @return float
     */
    public function getProductAmount(): float
    {
        $product = $this->getProduct();
        if ($product) {
            return $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
        }
        return 0;
    }

    /**
     * @param $product
     * @return float
     */
    public function getDiscountAmount($product): float
    {
        $discountAmount = 0;
        try {
            $discountAmount = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue() - $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        } catch (NoSuchEntityException $e) {
            return 0;
        }
        return $discountAmount;
    }

}
