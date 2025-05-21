<?php

namespace Amwal\Payments\Block\Product;

use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\Config\Source\ModuleType;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogWidget\Model\Rule;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Rule\Model\Condition\Sql\Builder as SqlBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Helper\Conditions;
use Psr\Log\LoggerInterface;

/**
 * Amwal Payments Product List Block
 *
 * Displays products in a grid with Amwal checkout buttons
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductsList extends \Magento\CatalogWidget\Block\Product\ProductsList
{
    /**
     * Amwal template file path
     */
    private const AMWAL_TEMPLATE_PATH = 'Amwal_Payments::product/widget/content/grid.phtml';

    /**
     * Default Magento template file path
     */
    private const DEFAULT_TEMPLATE_PATH = 'Magento_CatalogWidget::product/widget/content/grid.phtml';

    /**
     * Checkout button ID prefix
     */
    public const CHECKOUT_BUTTON_ID_PREFIX = 'amwal-checkout-button-';

    /**
     * Amwal checkout button ID prefix
     */
    public const AMWAL_CHECKOUT_BUTTON_ID_PREFIX = 'amwal-checkout';

    /**
     * Default ID length
     */
    private const DEFAULT_ID_LENGTH = 8;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Random
     */
    private Random $mathRandom;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param HttpContext $httpContext
     * @param SqlBuilder $sqlBuilder
     * @param Rule $rule
     * @param Conditions $conditionsHelper
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param Random $mathRandom
     * @param LoggerInterface $logger
     * @param array $data
     * @param Json|null $json
     * @param LayoutFactory|null $layoutFactory
     * @param EncoderInterface|null $urlEncoder
     * @param CategoryRepositoryInterface|null $categoryRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        Visibility $catalogProductVisibility,
        HttpContext $httpContext,
        SqlBuilder $sqlBuilder,
        Rule $rule,
        Conditions $conditionsHelper,
        Config $config,
        StoreManagerInterface $storeManager,
        Random $mathRandom,
        LoggerInterface $logger,
        array $data = [],
        Json $json = null,
        LayoutFactory $layoutFactory = null,
        EncoderInterface $urlEncoder = null,
        CategoryRepositoryInterface $categoryRepository = null
    ) {
        parent::__construct(
            $context,
            $productCollectionFactory,
            $catalogProductVisibility,
            $httpContext,
            $sqlBuilder,
            $rule,
            $conditionsHelper,
            $data,
            $json,
            $layoutFactory,
            $urlEncoder,
            $categoryRepository
        );

        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->mathRandom = $mathRandom;
        $this->logger = $logger;
    }

    /**
     * Get unique ID for checkout button
     *
     * @param int $length
     * @return string
     * @throws LocalizedException
     */
    public function getUniqueId(int $length = self::DEFAULT_ID_LENGTH): string
    {
        try {
            return self::CHECKOUT_BUTTON_ID_PREFIX . '-' . $this->mathRandom->getRandomString($length);
        } catch (\Exception $e) {
            $this->logger->error('Failed to generate unique ID: ' . $e->getMessage());
            throw new LocalizedException(__('Unable to generate unique ID for checkout button'));
        }
    }

    /**
     * Get Amwal checkout button ID
     *
     * @param int $length
     * @return string
     */
    public function getCheckoutButtonId(int $length = self::DEFAULT_ID_LENGTH): string
    {
        return self::AMWAL_CHECKOUT_BUTTON_ID_PREFIX . '-' . $this->mathRandom->getRandomString($length);
    }

    /**
     * Get trigger context
     *
     * @return string
     */
    public function getTriggerContext(): string
    {
        return 'product-list-widget';
    }

    /**
     * Get locale from configuration
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->config->getLocale();
    }

    /**
     * Get current store code
     *
     * @return string
     */
    public function getStoreCode(): string
    {
        try {
            return $this->storeManager->getStore()->getCode();
        } catch (NoSuchEntityException $e) {
            $this->logger->warning('Could not retrieve store code: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Check if the widget should render
     *
     * This method checks:
     * 1. If the module is not in Lite mode
     * 2. If Amwal payment is active
     * 3. If express checkout is enabled
     * 4. If the widget is enabled (new setting)
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        // Check if the module is Lite
        if ($this->config->getModuleType() === ModuleType::MODULE_TYPE_LITE) {
            return false;
        }

        // Check if the widget is explicitly disabled
        if (!$this->config->isProductListWidgetEnabled()) {
            return false;
        }

        // Check if the configuration is active and express checkout is enabled
        return $this->config->isActive() && $this->config->isExpressCheckoutActive();
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->shouldRender()) {
            $this->setTemplate(self::DEFAULT_TEMPLATE_PATH);
            return parent::_toHtml();
        }
        $this->setTemplate(self::AMWAL_TEMPLATE_PATH);
        return parent::_toHtml();
    }
}
