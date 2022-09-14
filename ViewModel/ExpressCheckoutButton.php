<?php
declare(strict_types=1);

namespace Amwal\Payments\ViewModel;

use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Api\Data\RefIdDataInterfaceFactory;
use Amwal\Payments\Api\RefIdManagementInterface;
use Amwal\Payments\Model\Config as AmwalConfig;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Helper\Data;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class ExpressCheckoutButton implements ArgumentInterface
{

    protected AmwalConfig $config;
    private Registry $registry;
    private Session $customerSession;
    private ScopeConfigInterface $scopeConfig;
    private RefIdManagementInterface $refIdManagement;
    protected RefIdDataInterfaceFactory $refIdDataFactory;
    private ?Product $product = null;
    private string $timestamp;

    /**
     * @param AmwalConfig $config
     * @param ScopeConfigInterface $scopeConfig
     * @param Registry $registry
     * @param Session $customerSession
     * @param RefIdManagementInterface $refIdManagement
     * @param RefIdDataInterfaceFactory $refIdDataFactory
     * @param int $timestamp
     */
    public function __construct(
        AmwalConfig $config,
        ScopeConfigInterface $scopeConfig,
        Registry $registry,
        Session $customerSession,
        RefIdManagementInterface $refIdManagement,
        RefIdDataInterfaceFactory $refIdDataFactory
    ) {
        $this->config = $config;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->refIdManagement = $refIdManagement;
        $this->refIdDataFactory = $refIdDataFactory;
        $this->timestamp = microtime();
    }

    /**
     * @return bool
     */
    public function isExpressCheckoutActive(): bool
    {
        if (!$this->config->isActive() || !$this->config->isExpressCheckoutActive()) {
            return false;
        }

        $guestCheckoutAllowed = $this->scopeConfig->isSetFlag(Data::XML_PATH_GUEST_CHECKOUT, ScopeInterface::SCOPE_STORE);

        if (!$guestCheckoutAllowed && !$this->customerSession->isLoggedIn()) {
            return false;
        }

        return true;
    }

    /**
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        return $this->product ?? $this->registry->registry('product');
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function isProductConfigurable(Product $product): bool
    {
        return $product->getTypeId() === Configurable::TYPE_CODE;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->config->getMerchantId();
    }

    /**
     * @return string
     */
    public function getMerchantMode(): string
    {
        return $this->config->getMerchantMode();
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->config->getCountryCode();
    }

    /**
     * @return bool
     */
    public function isDarkModeEnabled(): bool
    {
        return $this->config->isDarkModeEnabled();
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
     */
    public function getLocale(): string
    {
        return $this->config->getLocale();
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @return int
     */
    public function getCustomerId(): int
    {
        return (int) $this->customerSession->getId();
    }

    /**
     * @return string
     */
    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    /**
     * @return string
     */
    public function getRefId(): string
    {
        return $this->refIdManagement->generateRefId($this->getRefIdData());
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getConfigurableOptions(Product $product): array
    {
        if (!$this->isProductConfigurable($product)) {
            return [];
        }
        return $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
    }

    /**
     * @param Product $product
     * @return void
     */
    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

    /**
     * @return RefIdDataInterface
     */
    public function getRefIdData(): RefIdDataInterface
    {
        return $this->refIdDataFactory->create()
            ->setSecret($this->config->getRefIdSecret())
            ->setIdentifier((string) $this->getProduct()->getId())
            ->setCustomerId($this->getCustomerId())
            ->setTimestamp($this->getTimestamp());
    }
}
