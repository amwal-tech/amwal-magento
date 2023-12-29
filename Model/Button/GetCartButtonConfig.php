<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Button;

use Amwal\Payments\Api\Data\AmwalAddressInterfaceFactory;
use Amwal\Payments\Api\Data\AmwalButtonConfigInterface;
use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Api\RefIdManagementInterface;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\Data\AmwalButtonConfig;
use Amwal\Payments\Model\Data\AmwalButtonConfigFactory;
use Amwal\Payments\Model\ThirdParty\CityHelper;
use Amwal\Payments\ViewModel\ExpressCheckoutButton;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\SessionFactory as CheckoutSessionFactory;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Store\Model\StoreManagerInterface;

class GetCartButtonConfig extends GetConfig
{
    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId;

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
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
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
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
    ) {
        parent::__construct(
            $buttonConfigFactory,
            $config,
            $viewModel,
            $storeManager,
            $customerSessionFactory,
            $checkoutSessionFactory,
            $cityHelper,
            $amwalAddressFactory,
            $refIdManagement,
            $cartRepository,
            $productRepository,
            $jsonSerializer,
            $regionCollectionFactory
        );
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
    }

    /**
     * @param RefIdDataInterface $refIdData
     * @param string|null $triggerContext
     * @param string|null $cartId
     * @return AmwalButtonConfigInterface
     * @throws LocalizedException
     */
    public function execute(
            RefIdDataInterface $refIdData,
            string $triggerContext = null,
            ?string $cartId = null
    ): AmwalButtonConfigInterface {
        /** @var AmwalButtonConfig $buttonConfig */
        $buttonConfig = $this->buttonConfigFactory->create();

        if ($cartId) {
            try {
                $quoteId = $this->maskedQuoteIdToQuoteId->execute($cartId);
                $quote = $this->cartRepository->get($quoteId);
            } catch (NoSuchEntityException $e) {
                throw new LocalizedException(__('We were unable to retrieve the quote. Please try again later'));
            }
        } else {
            try {
                $quote = $this->checkoutSessionFactory->create()->getQuote();
            } catch (NoSuchEntityException|LocalizedException $e) {
                throw new LocalizedException(__('We were unable to retrieve the quote. Please try again later'));
            }
            if ($quote->getId()) {
                try {
                    $cartId = $this->quoteIdToMaskedQuoteId->execute((int) $quote->getId());
                } catch (NoSuchEntityException $e) {
                    throw new LocalizedException(__('We were unable to retrieve the quote. Please try again later'));
                }
            }
            $buttonConfig->setCartId($cartId);
        }

        $this->addGenericButtonConfig($buttonConfig, $refIdData, $quote);

        $buttonConfig->setAmount($this->getAmount($quote));
        $buttonConfig->setId($this->getButtonId($cartId));

        if ($limitedCities = $this->getCityCodesJson()) {
            $buttonConfig->setAllowedAddressCities($limitedCities);
        }
        if ($limitedRegions = $this->getLimitedRegionCodesJson()) {
            $buttonConfig->setAllowedAddressStates($limitedRegions);
        }

        if ($triggerContext === 'regular-checkout') {
            $this->addRegularCheckoutButtonConfig($buttonConfig, $quote);
        }
        return $buttonConfig;
    }

    /**
     * @param Quote $quote
     * @return float
     */
    private function getAmount(Quote $quote): float
    {
        return (float)$quote->getGrandTotal();
    }

    /**
     * @param AmwalButtonConfigInterface $buttonConfig
     * @param Quote $quote
     * @return void
     */
    public function addRegularCheckoutButtonConfig(AmwalButtonConfigInterface $buttonConfig, Quote $quote): void
    {
        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();
        $customer = $quote->getCustomer();

        $street = $shippingAddress->getStreet()[0] ?? '';
        $street2 = $shippingAddress->getStreet()[1] ?? '';
        $addressComponents = [
            'street1' => $street,
            'street2' => $street2,
            'city' => $shippingAddress->getCity(),
            'state' => $shippingAddress->getRegion() ?? $shippingAddress->getCity(),
            'country' => $shippingAddress->getCountryId(),
            'postcode' => $shippingAddress->getPostcode()
        ];
        $formattedAddress = json_encode($addressComponents);

        $buttonConfig->setInitialAddress($formattedAddress);
        $buttonConfig->setInitialEmail($shippingAddress->getEmail() ?? $billingAddress->getEmail() ?? $customer->getEmail() ?? null);
        $buttonConfig->setInitialPhone($shippingAddress->getTelephone() ?? $billingAddress->getTelephone() ?? null);
        $buttonConfig->setInitialFirstName($shippingAddress->getFirstname() ?? $billingAddress->getFirstname() ?? $customer->getFirstname() ?? null);
        $buttonConfig->setInitialLastName($shippingAddress->getLastname() ?? $billingAddress->getLastname() ?? $customer->getLastname() ?? null);
        $buttonConfig->setAddressRequired(false);
        $buttonConfig->setEnablePrePayTrigger(true);
        $buttonConfig->setEnablePreCheckoutTrigger(false);
    }


    /**
     * @return array
     */
    public function getCityCodes(): array
    {
        $cityCodes = $this->cityHelper->getCityCodes();
        if (!$cityCodes) {
            return [];
        }
        return $cityCodes;
    }

    /**
     * @return string
     */
    protected function getCityCodesJson(): string
    {
        $cityCodes = $this->getCityCodes();

        if (!$cityCodes) {
            return '';
        }

        return $this->jsonSerializer->serialize($cityCodes);
    }

    /**
     * @return string
     */
    protected function getLimitedRegionCodesJson(): string
    {
        return $this->jsonSerializer->serialize(
            $this->getLimitedRegionsArray()
        );
    }

    /**
     * @return array
     */
    public function getLimitedRegionsArray(): array
    {

        $limitedRegionCodes = [];
        $limitedRegions = $this->config->getLimitedRegions();
        $regionCollection = $this->regionCollectionFactory->create();
        $regionCollection->addFieldToFilter('main_table.region_id', ['in' => $limitedRegions]);
        foreach ($regionCollection->getItems() as $region) {
            $regionName = $region->getName();
            $limitedRegionCodes[$region->getCountryId()][$region->getRegionId()] = $regionName;
        }

        return $limitedRegionCodes;
    }
}
