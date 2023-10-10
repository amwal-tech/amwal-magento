<?php

namespace Amwal\Payments\Block\Widget;

use Amwal\Payments\Model\Config;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\Math\Random;
use Magento\Catalog\Block\Product\View\Options as ProductOptionsBlock;
use Magento\Catalog\Model\Product;

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
     * @var ProductFactory
     */
    protected ProductFactory $productFactory;
    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;
    /**
     * @var Random
     */
    protected Random $random;

    public const CHECKOUT_BUTTON_ID_PREFIX = 'amwal-checkout-button-';

    public function __construct(
        Context                     $context,
        PostHelper                  $postDataHelper,
        Resolver                    $layerResolver,
        ProductRepositoryInterface $productRepository,
        ProductFactory              $productFactory,
        CategoryRepositoryInterface $categoryRepository,
        Config                      $config,
        Random                      $random,
        StoreManagerInterface       $storeManager,
        Data                        $urlHelper, array $data = []
    )
    {
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->random = $random;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->setTemplate("Amwal_Payments::express/widget-checkout-button.phtml");
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    public function getProduct(): Product
    {
        $productId = $this->getProduct_id();
        if ($productId) {
            $productId = str_replace('product/', '', $productId);
        }
        $product = $this->productRepository->getById($productId);
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

}
