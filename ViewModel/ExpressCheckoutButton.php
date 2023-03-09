<?php
declare(strict_types=1);

namespace Amwal\Payments\ViewModel;

use Amwal\Payments\Api\Data\AmwalAddressInterfaceFactory;
use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Api\Data\RefIdDataInterfaceFactory;
use Amwal\Payments\Api\RefIdManagementInterface;
use Amwal\Payments\Model\Config as AmwalConfig;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Helper\Data;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Session;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Locale\Resolver as LocaleResolver;

class ExpressCheckoutButton implements ArgumentInterface
{

    protected AmwalConfig $config;
    private Registry $registry;
    private Session $customerSession;
    private ScopeConfigInterface $scopeConfig;
    private RefIdManagementInterface $refIdManagement;
    protected RefIdDataInterfaceFactory $refIdDataFactory;
    private ?Product $product = null;
    private DirectoryHelper $directoryHelper;
    private Json $jsonSerializer;
    private AmwalAddressInterfaceFactory $amwalAddressFactory;
    private RegionCollectionFactory $regionCollectionFactory;
    private string $timestamp;
    private LocaleResolver $localeResolver;
    private ResourceConnection $resourceConnection;

    /**
     * @param AmwalConfig $config
     * @param ScopeConfigInterface $scopeConfig
     * @param Registry $registry
     * @param Session $customerSession
     * @param RefIdManagementInterface $refIdManagement
     * @param RefIdDataInterfaceFactory $refIdDataFactory
     * @param DirectoryHelper $directoryHelper
     * @param Json $jsonSerializer
     * @param AmwalAddressInterfaceFactory $amwalAddressFactory
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param LocaleResolver $localeResolver
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        AmwalConfig $config,
        ScopeConfigInterface $scopeConfig,
        Registry $registry,
        Session $customerSession,
        RefIdManagementInterface $refIdManagement,
        RefIdDataInterfaceFactory $refIdDataFactory,
        DirectoryHelper $directoryHelper,
        Json $jsonSerializer,
        AmwalAddressInterfaceFactory $amwalAddressFactory,
        RegionCollectionFactory $regionCollectionFactory,
        LocaleResolver $localeResolver,
        ResourceConnection $resourceConnection
    ) {
        $this->config = $config;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->refIdManagement = $refIdManagement;
        $this->refIdDataFactory = $refIdDataFactory;
        $this->directoryHelper = $directoryHelper;
        $this->jsonSerializer = $jsonSerializer;
        $this->amwalAddressFactory = $amwalAddressFactory;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->localeResolver = $localeResolver;
        $this->resourceConnection = $resourceConnection;
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

        /*$guestCheckoutAllowed = $this->scopeConfig->isSetFlag(Data::XML_PATH_GUEST_CHECKOUT, ScopeInterface::SCOPE_STORE);

        if (!$guestCheckoutAllowed && !$this->customerSession->isLoggedIn()) {
            return false;
        }*/

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
            ->setIdentifier((string) $this->getProduct()->getId())
            ->setCustomerId($this->getCustomerId())
            ->setTimestamp($this->getTimestamp());
    }

    /**
     * @return string
     */
    public function getAllowedCountriesJson(): string
    {
        return $this->jsonSerializer->serialize(
            array_keys($this->directoryHelper->getCountryCollection()->getItems())
        );
    }

    /**
     * @return array
     */
    public function getInitialDataAttributes(): array
    {
        $customer = $this->customerSession->getCustomer();

        $defaultShippingAddress = $customer->getDefaultShippingAddress();
        if (!$defaultShippingAddress) {
            return [];
        }

        $initialAddress = $this->amwalAddressFactory->create();
        $initialAddress->setCity($defaultShippingAddress->getCity() ?? '');
        $initialAddress->setState($defaultShippingAddress->getRegionCode() ?? '');
        $initialAddress->setPostcode($defaultShippingAddress->getPostcode() ?? '');
        $initialAddress->setCountry($defaultShippingAddress->getCountryId() ?? '');
        $initialAddress->setStreet1($defaultShippingAddress->getStreetLine(1) ?? '');
        $initialAddress->setStreet2($defaultShippingAddress->getStreetLine(2) ?? '');
        $initialAddress->setEmail($customer->getEmail() ?? '');

        $attributes = [];
        $attributes['initial-address'] = $initialAddress->toJson();
        $attributes['initial-email'] = $customer->getEmail() ?? '';
        $attributes['initial-phone'] = $defaultShippingAddress->getTelephone() ?? '';

        return $attributes;
    }

    /**
     * @return string
     */
    public function getLimitedRegionCodesJson(): string
    {
        $limitedRegionCodes = [];
        $limitedRegions = $this->config->getLimitedRegions();
        $regionCollection = $this->regionCollectionFactory->create();
        $regionCollection->addFieldToFilter('main_table.region_id', ['in' => $limitedRegions]);
        foreach ($regionCollection->getItems() as $region) {
            $limitedRegionCodes[$region->getCountryId()][$region->getRegionId()] = $region->getName();
        }

        return $this->jsonSerializer->serialize($limitedRegionCodes);
    }

    /**
     * @return string
     */
    public function getCityCodesJson(): string
    {
        $locale = $this->localeResolver->getLocale();
        $cityCodes = [];
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('directory_country_region_city');
        $localeCityTableName = $this->resourceConnection->getTableName('directory_country_region_city_name');

        if (!$connection->isTableExists($tableName) || !$connection->isTableExists($localeCityTableName)) {
            return '';
        }

        $condition = $connection->quoteInto('lng.locale = ?', $locale);
        $sql = $connection->select()->from(
            ['city' => $tableName]
        )->joinLeft(
            ['lng' => $localeCityTableName],
            "city.city_id = lng.city_id AND {$condition}",
            ['name']
        );

        foreach ($connection->fetchAll($sql) as $city) {
            $cityCodes[$city['country_id']][$city['region_id']][]  = $city['name'] ?? $city['default_name'];
        }

        return $this->jsonSerializer->serialize($cityCodes);
    }
}
