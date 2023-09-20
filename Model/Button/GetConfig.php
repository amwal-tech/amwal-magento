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
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Amwal\Payments\ViewModel\ExpressCheckoutButton;
use libphonenumber\PhoneNumberUtil;
use Magento\Framework\Locale\ResolverInterface;

class GetConfig
{
    protected AmwalButtonConfigFactory $buttonConfigFactory;
    protected Config $config;
    protected ExpressCheckoutButton $viewModel;
    protected StoreManagerInterface $storeManager;
    protected CustomerSessionFactory $customerSessionFactory;
    protected CheckoutSessionFactory $checkoutSessionFactory;
    protected CityHelper $cityHelper;
    protected DirectoryHelper $directoryHelper;
    protected AmwalAddressInterfaceFactory $amwalAddressFactory;
    protected RefIdManagementInterface $refIdManagement;
    protected CartRepositoryInterface $cartRepository;
    protected ProductRepositoryInterface $productRepository;
    protected Json $jsonSerializer;
    protected ResolverInterface $localeResolver;

    /**
     * @param AmwalButtonConfigFactory $buttonConfigFactory
     * @param Config $config
     * @param ExpressCheckoutButton $viewModel
     * @param StoreManagerInterface $storeManager
     * @param CustomerSessionFactory $customerSessionFactory
     * @param CheckoutSessionFactory $checkoutSessionFactory
     * @param CityHelper $cityHelper
     * @param DirectoryHelper $directoryHelper
     * @param AmwalAddressInterfaceFactory $amwalAddressFactory
     * @param RefIdManagementInterface $refIdManagement
     * @param CartRepositoryInterface $cartRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Json $jsonSerializer
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        AmwalButtonConfigFactory $buttonConfigFactory,
        Config $config,
        ExpressCheckoutButton $viewModel,
        StoreManagerInterface $storeManager,
        CustomerSessionFactory $customerSessionFactory,
        CheckoutSessionFactory $checkoutSessionFactory,
        CityHelper $cityHelper,
        DirectoryHelper $directoryHelper,
        AmwalAddressInterfaceFactory $amwalAddressFactory,
        RefIdManagementInterface $refIdManagement,
        CartRepositoryInterface $cartRepository,
        ProductRepositoryInterface $productRepository,
        Json $jsonSerializer,
        ResolverInterface $localeResolver
    ) {
        $this->buttonConfigFactory = $buttonConfigFactory;
        $this->config = $config;
        $this->viewModel = $viewModel;
        $this->storeManager = $storeManager;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        $this->cityHelper = $cityHelper;
        $this->directoryHelper = $directoryHelper;
        $this->amwalAddressFactory = $amwalAddressFactory;
        $this->refIdManagement = $refIdManagement;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->jsonSerializer = $jsonSerializer;
        $this->localeResolver = $localeResolver;
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

        if ($limitedRegions = $this->getLimitedRegionCodesJson()) {
            $buttonConfig->setAllowedAddressStates($limitedRegions);
        }
        if ($limitedCities = $this->getCityCodesJson()) {
            $buttonConfig->setAllowedAddressCities($limitedCities);
        }

        $buttonConfig->setCountryCode($this->config->getCountryCode());
        $buttonConfig->setDarkMode($this->config->isDarkModeEnabled() ? 'on' : 'off');
        $buttonConfig->setEmailRequired(!$customerSession->isLoggedIn());
        $buttonConfig->setEnablePreCheckoutTrigger(true);
        $buttonConfig->setEnablePrePayTrigger(true);
        $buttonConfig->setMerchantId($this->config->getMerchantId());
        $buttonConfig->setRefId($this->refIdManagement->generateRefId($refIdData));
        $buttonConfig->setTestEnvironment($this->config->getMerchantMode() === MerchantMode::MERCHANT_TEST_MODE ? 'qa' : null);
        $buttonConfig->setPluginVersion($this->config->getVersion());
        $buttonConfig->setQuoteId($this->checkoutSessionFactory->create()->getQuote()->getId());
        $buttonConfig->setPostCodeOptionalCountries($this->config->getPostCodeOptionalCountries());
        $buttonConfig->setInstallmentOptionsUrl($this->config->getInstallmentOptionsUrl());

        $initialAddressData = $this->getInitialAddressData($customerSession);
        if ($initialAddressData) {
            $buttonConfig->setInitialAddress($initialAddressData['address']);
            $buttonConfig->setInitialPhone($initialAddressData['phone']);
            if (isset($initialAddressData['country'])) {
                $buttonConfig->setInitialPhone($this->phoneFormat($initialAddressData['phone'], $initialAddressData['country'] ));
            }
            $buttonConfig->setInitialEmail($initialAddressData['email']);
            $buttonConfig->setInitialFirstName($initialAddressData['firstname']);
            $buttonConfig->setInitialLastName($initialAddressData['lastname']);
        }

    }

    /**
     * @return string
     */
    protected function getLimitedRegionCodesJson(): string
    {
        return $this->jsonSerializer->serialize(
            $this->config->getLimitedRegionsArray()
        );
    }

    /**
     * @return string
     */
    protected function getCityCodesJson(): string
    {
        $cityCodes = $this->cityHelper->getCityCodes();

        if (!$cityCodes) {
            return '';
        }

        return $this->jsonSerializer->serialize($cityCodes);
    }

    /**
     * @param Session $customerSession
     * @return array
     */
    protected function getInitialAddressData(Session $customerSession)
    {
        $customer = $customerSession->getCustomer();

        $addressData = $this->checkoutSessionFactory->create()->getQuote()->getShippingAddress();
        if (!$addressData->getCity()) {
            $addressData = $customer->getDefaultShippingAddress();
            if (!$addressData) {
                return [];
            }
        }
        $billingAddressData = $this->checkoutSessionFactory->create()->getQuote()->getBillingAddress();
        if (!$billingAddressData->getCity()) {
            $billingAddressData = $customer->getDefaultBillingAddress();
            if (!$billingAddressData) {
                return [];
            }
        }
        $initialAddress = $this->amwalAddressFactory->create();
        $initialAddress->setCity($addressData->getCity() ?? $billingAddressData->getCity());
        $initialAddress->setState($addressData->getRegionCode() ?? $billingAddressData->getRegionCode() ?? 'N/A');
        $initialAddress->setPostcode($addressData->getPostcode() ?? $billingAddressData->getPostcode());
        $initialAddress->setCountry($addressData->getCountryId() ?? $billingAddressData->getCountryId());
        $initialAddress->setStreet1($addressData->getStreetLine(1) ?? $billingAddressData->getStreetLine(1));
        $initialAddress->setStreet2($addressData->getStreetLine(2) ?? $billingAddressData->getStreetLine(2));
        $initialAddress->setEmail($customer->getEmail() ??  $addressData->getEmail() ?? $billingAddressData->getEmail());

        $attributes = [];
        $attributes['address']   = $initialAddress->toJson();
        $attributes['email']     = $customer->getEmail() ??  $addressData->getEmail() ?? $billingAddressData->getEmail();
        $attributes['phone']     = $addressData->getTelephone() ?? $billingAddressData->getTelephone();
        $attributes['country']   = $addressData->getCountryId() ?? $billingAddressData->getCountryId();
        $attributes['firstname'] = $addressData->getFirstname() ?? $billingAddressData->getFirstname();
        $attributes['lastname']  = $addressData->getLastname()  ?? $billingAddressData->getLastname();

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


    protected function phoneFormat($phone_number, $country)
    {
        if (strpos($phone_number, '+') === 0) {
            return $phone_number;
        }
        if (class_exists('libphonenumber\PhoneNumberUtil')) {
            $phoneNumberUtil = PhoneNumberUtil::getInstance();
            try {
                $phoneNumberProto = $phoneNumberUtil->parse($phone_number, $country);
                $phone_number = $phoneNumberUtil->format($phoneNumberProto, \libphonenumber\PhoneNumberFormat::E164);
            } catch (\libphonenumber\NumberParseException $e) {}
        }
        return $phone_number;
    }
}
