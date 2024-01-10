<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Button;

use Amwal\Payments\Api\Data\AmwalAddressInterface;
use Amwal\Payments\Api\Data\AmwalAddressInterfaceFactory;
use Amwal\Payments\Api\Data\AmwalButtonConfigInterface;
use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Api\RefIdManagementInterface;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\Config\Source\MerchantMode;
use Amwal\Payments\Model\Data\AmwalButtonConfig;
use Amwal\Payments\Model\Data\AmwalButtonConfigFactory;
use Amwal\Payments\Model\ThirdParty\CityHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\SessionFactory as CheckoutSessionFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
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
    protected AmwalAddressInterfaceFactory $amwalAddressFactory;
    protected RefIdManagementInterface $refIdManagement;
    protected CartRepositoryInterface $cartRepository;
    protected ProductRepositoryInterface $productRepository;
    protected Json $jsonSerializer;
    protected RegionCollectionFactory $regionCollectionFactory;
    protected RegionFactory $regionFactory;
    protected QuoteIdMaskFactory $quoteIdMaskFactory;

    /**
     * @param AmwalButtonConfigFactory $buttonConfigFactory
     * @param Config $config
     * @param ExpressCheckoutButton $viewModel
     * @param StoreManagerInterface $storeManager
     * @param CustomerSessionFactory $customerSessionFactory
     * @param CheckoutSessionFactory $checkoutSessionFactory
     * @param CityHelper $cityHelper
     * @param AmwalAddressInterfaceFactory $amwalAddressFactory
     * @param RefIdManagementInterface $refIdManagement
     * @param CartRepositoryInterface $cartRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Json $jsonSerializer
     * @param RegionCollectionFactory $regionCollectionFactory
     *
     */
    public function __construct(
        AmwalButtonConfigFactory $buttonConfigFactory,
        Config $config,
        ExpressCheckoutButton $viewModel,
        StoreManagerInterface $storeManager,
        CustomerSessionFactory $customerSessionFactory,
        CheckoutSessionFactory $checkoutSessionFactory,
        CityHelper $cityHelper,
        AmwalAddressInterfaceFactory $amwalAddressFactory,
        RefIdManagementInterface $refIdManagement,
        CartRepositoryInterface $cartRepository,
        ProductRepositoryInterface $productRepository,
        Json $jsonSerializer,
        RegionCollectionFactory $regionCollectionFactory,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->buttonConfigFactory = $buttonConfigFactory;
        $this->config = $config;
        $this->viewModel = $viewModel;
        $this->storeManager = $storeManager;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        $this->cityHelper = $cityHelper;
        $this->amwalAddressFactory = $amwalAddressFactory;
        $this->refIdManagement = $refIdManagement;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->jsonSerializer = $jsonSerializer;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * @param AmwalButtonConfig $buttonConfig
     * @param RefIdDataInterface $refIdData
     * @param Quote $quote
     * @param Session $customerSession
     * @param AmwalAddressInterface $initialAddress
     * @return void
     */
    public function addGenericButtonConfig(AmwalButtonConfig $buttonConfig, RefIdDataInterface $refIdData, Quote $quote, Session $customerSession, AmwalAddressInterface $initialAddress): void
    {
        $buttonConfig->setLabel('quick-buy');
        $buttonConfig->setAddressHandshake(true);
        $buttonConfig->setAddressRequired(true);
        $buttonConfig->setShowPaymentBrands(true);
        $buttonConfig->setDisabled(true);
        $buttonConfig->setAllowedAddressCountries($this->config->getAllowedAddressCountries());


        $buttonConfig->setCountryCode($this->config->getCountryCode());
        $buttonConfig->setDarkMode($this->config->isDarkModeEnabled() ? 'on' : 'off');
        $buttonConfig->setEmailRequired(!$customerSession->isLoggedIn());
        $buttonConfig->setEnablePreCheckoutTrigger(true);
        $buttonConfig->setEnablePrePayTrigger(true);
        $buttonConfig->setMerchantId($this->config->getMerchantId());
        $buttonConfig->setRefId($this->refIdManagement->generateRefId($refIdData));
        $buttonConfig->setTestEnvironment($this->config->getMerchantMode() === MerchantMode::MERCHANT_TEST_MODE ? 'qa' : null);
        $buttonConfig->setPluginVersion($this->config->getVersion());
        $buttonConfig->setPostCodeOptionalCountries($this->config->getPostCodeOptionalCountries());
        $buttonConfig->setInstallmentOptionsUrl($this->config->getInstallmentOptionsUrl());

        $initialAddressData = $this->getInitialAddressData($customerSession, $quote, $initialAddress);
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
     * @param Session $customerSession
     * @param Quote $quote
     * @param AmwalAddressInterface $initialAddress
     * @return array
     */
    public function getInitialAddressData(Session $customerSession, Quote $quote, AmwalAddressInterface $initialAddress): array
    {
        $customer = $customerSession->getCustomer();

        $addressData = $quote->getShippingAddress();
        if (!$addressData->getCity()) {
            $addressData = $customer->getDefaultShippingAddress();
            if (!$addressData) {
                return [];
            }
        }
        $billingAddressData = $quote->getBillingAddress();
        if (!$billingAddressData->getCity()) {
            $billingAddressData = $customer->getDefaultBillingAddress();
            if (!$billingAddressData) {
                return [];
            }
        }

        $initialAddress->setCity($addressData->getCity() ?? $billingAddressData->getCity());
        $initialAddress->setState($addressData->getRegionCode() ?? $billingAddressData->getRegionCode() ?? 'N/A');
        $initialAddress->setPostcode($addressData->getPostcode() ?? $billingAddressData->getPostcode());
        $initialAddress->setCountry($addressData->getCountryId() ?? $billingAddressData->getCountryId());
        $initialAddress->setStreet1($addressData->getStreetLine(1) ?? $billingAddressData->getStreetLine(1));
        $initialAddress->setStreet2($addressData->getStreetLine(2) ?? $billingAddressData->getStreetLine(2));
        $initialAddress->setEmail($customer->getEmail() ??  $addressData->getEmail() ?? $billingAddressData->getEmail());

        $attributes = [];
        $attributes['address']   = $this->jsonSerializer->serialize(
            [
                'city'      => $initialAddress->getCity(),
                'state'     => $initialAddress->getState(),
                'postcode'  => $initialAddress->getPostcode(),
                'country'   => $initialAddress->getCountry(),
                'street1'   => $initialAddress->getStreet1(),
                'street2'   => $initialAddress->getStreet2(),
                'email'     => $initialAddress->getEmail(),
            ]
        );
        $attributes['email']     = $customer->getEmail() ??  $addressData->getEmail() ?? $billingAddressData->getEmail();
        $attributes['phone']     = $addressData->getTelephone() ?? $billingAddressData->getTelephone();
        $attributes['country']   = $addressData->getCountryId() ?? $billingAddressData->getCountryId();
        $attributes['firstname'] = $customer->getFirstname() ?? $addressData->getFirstname() ?? $billingAddressData->getFirstname();
        $attributes['lastname']  = $customer->getLastname() ?? $addressData->getLastname()  ?? $billingAddressData->getLastname();

        return $attributes;
    }

    /**
     * @param string|null $cartId
     * @return string
     */
    public function getButtonId(?string $cartId): string
    {
        $id = AmwalButtonConfigInterface::ID_PREFIX;
        if ($cartId) {
            return $id . $cartId;
        }
        return $id . 'newquote';
    }


    /**
     * @param string $phone_number
     * @param string $country
     * @return string
     */
    public function phoneFormat($phone_number, $country)
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
