<?php

namespace Amwal\Payments\Block\Product;


use Amwal\Payments\Model\Config;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogWidget\Model\Rule;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Rule\Model\Condition\Sql\Builder as SqlBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Helper\Conditions;

class ProductsList extends \Magento\CatalogWidget\Block\Product\ProductsList
{
    protected $_template = 'Amwal_Payments::product/widget/content/grid.phtml';

    public const CHECKOUT_BUTTON_ID_PREFIX = 'amwal-checkout-button-';

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;


    /**
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param HttpContext $httpContext
     * @param SqlBuilder $sqlBuilder
     * @param Rule $rule
     * @param Conditions $conditionsHelper
     * @param Config $config
     * @param array $data
     * @param Json|null $json
     * @param LayoutFactory|null $layoutFactory
     * @param EncoderInterface|null $urlEncoder
     * @param CategoryRepositoryInterface|null $categoryRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context                     $context,
        CollectionFactory           $productCollectionFactory,
        Visibility                  $catalogProductVisibility,
        HttpContext                 $httpContext,
        SqlBuilder                  $sqlBuilder,
        Rule                        $rule,
        Conditions                  $conditionsHelper,
        Config                      $config,
        StoreManagerInterface       $storeManager,
        array                       $data = [],
        Json                        $json = null,
        LayoutFactory               $layoutFactory = null,
        EncoderInterface            $urlEncoder = null,
        CategoryRepositoryInterface $categoryRepository = null
    ) {
        parent::__construct($context, $productCollectionFactory, $catalogProductVisibility, $httpContext, $sqlBuilder, $rule, $conditionsHelper, $data, $json, $layoutFactory, $urlEncoder, $categoryRepository);
        $this->config = $config;
        $this->storeManager = $storeManager;
    }


    /**
     * @return string
     * @throws LocalizedException
     */
    public function getUniqueId($length = 8): string
    {
        $randomInstance = ObjectManager::getInstance()->get(Random::class);
        return self::CHECKOUT_BUTTON_ID_PREFIX . '-' . $randomInstance->getRandomString($length);
    }

    /**
     * @return string
     */
    public function getTriggerContext(): string
    {
        return 'product-list-widget';
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
     * @return bool
     */
    private function shouldRender(): bool
    {
        $config = ObjectManager::getInstance()->get(Config::class);
        if (!$config->isActive() || !$config->isExpressCheckoutActive()) {
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
