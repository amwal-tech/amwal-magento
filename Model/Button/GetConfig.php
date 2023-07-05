<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Button;

use Amwal\Payments\Api\Data\AmwalAddressInterfaceFactory;
use Amwal\Payments\Api\Data\AmwalButtonConfigInterface;
use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Api\RefIdManagementInterface;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\Config\Source\MerchantMode;
use Amwal\Payments\Model\Data\AmwalButtonConfig;
use Amwal\Payments\Model\Data\AmwalButtonConfigFactory;
use Amwal\Payments\Model\ThirdParty\CityHelper;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\SessionFactory as CheckoutSessionFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class GetConfig
{
    protected AmwalButtonConfigFactory $buttonConfigFactory;
    protected Config $config;
    protected StoreManagerInterface $storeManager;
    protected CustomerSessionFactory $customerSessionFactory;
    protected CheckoutSessionFactory $checkoutSessionFactory;
    protected CityHelper $cityHelper;
    protected DirectoryHelper $directoryHelper;
    protected AmwalAddressInterfaceFactory $amwalAddressFactory;
    protected RefIdManagementInterface $refIdManagement;
    protected CartRepositoryInterface $cartRepository;
    protected ProductRepositoryInterface $productRepository;

    public function __construct(
        AmwalButtonConfigFactory $buttonConfigFactory,
        Config                   $config,
        StoreManagerInterface    $storeManager,
        CustomerSessionFactory   $customerSessionFactory,
        CheckoutSessionFactory   $checkoutSessionFactory,
        CityHelper               $cityHelper,
        DirectoryHelper          $directoryHelper,
        AmwalAddressInterfaceFactory $amwalAddressFactory,
        RefIdManagementInterface $refIdManagement,
        CartRepositoryInterface $cartRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->buttonConfigFactory = $buttonConfigFactory;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        $this->cityHelper = $cityHelper;
        $this->directoryHelper = $directoryHelper;
        $this->amwalAddressFactory = $amwalAddressFactory;
        $this->refIdManagement = $refIdManagement;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @param AmwalButtonConfig $buttonConfig
     * @param RefIdDataInterface $refIdData
     * @return void
     */
    protected function addGenericButtonConfig(AmwalButtonConfig $buttonConfig, RefIdDataInterface $refIdData): void
    {
        $customerSession = $this->customerSessionFactory->create();

        $buttonConfig->setLabel('quick-buy');
        $buttonConfig->setAddressHandshake(true);
        $buttonConfig->setAddressRequired(true);
        $buttonConfig->setShowPaymentBrands(true);
        $buttonConfig->setDisabled(true);
        $buttonConfig->setAllowedAddressCountries(array_keys($this->directoryHelper->getCountryCollection()->getItems()));
        $buttonConfig->setAllowedAddressStates($this->config->getLimitedRegionsArray());
        $buttonConfig->setAllowedAddressCities($this->cityHelper->getCityCodes());
        $buttonConfig->setLocale($this->config->getLocale());
        $buttonConfig->setCountryCode($this->config->getCountryCode());
        $buttonConfig->setDarkMode($this->config->isDarkModeEnabled() ? 'on' : 'off');
        $buttonConfig->setEmailRequired(!$customerSession->isLoggedIn());
        $buttonConfig->setEnablePreCheckoutTrigger(true);
        $buttonConfig->setEnablePrePayTrigger(true);
        $buttonConfig->setMerchantId($this->config->getMerchantId());
        $buttonConfig->setRefId($this->refIdManagement->generateRefId($refIdData));
        $buttonConfig->setTestEnvironment($this->config->getMerchantMode() === MerchantMode::MERCHANT_TEST_MODE ? 'qa' : null);
        $buttonConfig->setPluginVersion($this->config->getVersion());

        $initialAddressData = $this->getInitialAddressData($customerSession);
        if ($initialAddressData) {
            $buttonConfig->setInitialAddress($initialAddressData['address']);
            $buttonConfig->setInitialPhone($initialAddressData['phone']);
            $buttonConfig->setInitialEmail($initialAddressData['email']);
        }
    }

    /**
     * @param Session $customerSession
     * @return array
     */
    protected function getInitialAddressData(Session $customerSession)
    {
        $customer = $customerSession->getCustomer();

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
        $attributes['address'] = $initialAddress->toJson();
        $attributes['email'] = $customer->getEmail() ?? '';
        $attributes['phone'] = $defaultShippingAddress->getTelephone() ?? '';

        return $attributes;
    }

    /**
     * @param int|null $entityId
     * @return string
     */
    protected function getButtonId(?int $entityId): string
    {
        $id = AmwalButtonConfigInterface::ID_PREFIX;
        if ($entityId) {
            return $id . $entityId;
        }

        return $id . 'newquote';
    }
}
