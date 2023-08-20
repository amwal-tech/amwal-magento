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
use Magento\Framework\Url\Helper\Data;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\Math\Random;
use Magento\Catalog\Block\Product\View\Options as ProductOptionsBlock;

class ExpressCheckoutButton extends ListProduct implements BlockInterface
{

    /**
     * @var Config
     */
    private Config $config;

    protected $_productFactory;

    protected $random;

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
        Data                        $urlHelper, array $data = [],
    )
    {
        $this->_productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->random = $random;
        $this->config = $config;
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
        $this->setTemplate("Amwal_Payments::express/widget-checkout-button.phtml");
    }

    public function getProduct()
    {
        $productId = $this->getProduct_id();
        if ($productId) {
            $productId = str_replace('product/', '', $productId);
        }
        $product = $this->productRepository->getById($productId);
        return $product;
    }

    public function getTriggerContext(): string
    {
        return 'amwal-widget';
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getUniqueId($length = 8): string
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
