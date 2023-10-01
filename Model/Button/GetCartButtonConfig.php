<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Button;

use Amwal\Payments\Api\Data\AmwalButtonConfigInterface;
use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Model\Data\AmwalButtonConfig;
use Amwal\Payments\Model\ThirdParty\CityHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ObjectManager;
use libphonenumber\PhoneNumberUtil;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Serialize\Serializer\Json;
class GetCartButtonConfig extends GetConfig
{
    protected Json $jsonSerializer;
    protected CityHelper $cityHelper;
    protected ResolverInterface $localeResolver;
    /**
     * @param RefIdDataInterface $refIdData
     * @param string|null $triggerContext
     * @param int|null $quoteId
     * @param string|null $locale
     * @return AmwalButtonConfigInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(RefIdDataInterface $refIdData, string $triggerContext = null, ?int $quoteId = null, string $locale = null): AmwalButtonConfigInterface
    {
        /** @var AmwalButtonConfig $buttonConfig */
        $buttonConfig = $this->buttonConfigFactory->create();

        $this->addGenericButtonConfig($buttonConfig, $refIdData);

        $buttonConfig->setAmount($this->getAmount($quoteId));
        $buttonConfig->setId($this->getButtonId($quoteId));

        if ($limitedCities = $this->getCityCodesJson($locale)) {
            $buttonConfig->setAllowedAddressCities($limitedCities);
        }
        if ($limitedRegions = $this->getLimitedRegionCodesJson($locale)) {
            $buttonConfig->setAllowedAddressStates($limitedRegions);
        }

        if ($triggerContext == 'regular-checkout') {
            $this->addRegularCheckoutButtonConfig($buttonConfig, $quoteId);
        }

        return $buttonConfig;
    }

    /**
     * @param int|null $quoteId
     * @return float
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getAmount(?int $quoteId): float
    {
        if ($quoteId) {
            $quote = $this->cartRepository->get($quoteId);
        } else {
            $quote = $this->checkoutSessionFactory->create()->getQuote();
        }

        return (float)$quote->getGrandTotal();
    }

    /**
     * @param AmwalButtonConfigInterface $buttonConfig
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function addRegularCheckoutButtonConfig(AmwalButtonConfigInterface $buttonConfig, $quoteId): void
    {
        if ($quoteId) {
            $quote = $this->cartRepository->get($quoteId);
        } else {
            $objectManager = ObjectManager::getInstance();
            $checkoutSession = $objectManager->get(Session::class);
            $quote = $checkoutSession->getQuote();
        }

        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();
        $customer = $quote->getCustomer();

        $street = $shippingAddress->getStreet()[0] ?? '';
        $street2 = $shippingAddress->getStreet()[1] ?? '';
        $formatedAddress = json_encode(['street1' => $street, 'street2' => $street2, 'city' => $shippingAddress->getCity(), 'state' => $shippingAddress->getRegion() ?? $shippingAddress->getCity(), 'country' => $shippingAddress->getCountryId(), 'postcode' => $shippingAddress->getPostcode()]);

        $buttonConfig->setAddressRequired(false);
        $buttonConfig->setInitialAddress($formatedAddress ?? null);
        $buttonConfig->setInitialEmail($shippingAddress->getEmail() ?? $billingAddress->getEmail() ?? $customer->getEmail() ?? null);
        $buttonConfig->setInitialPhone($shippingAddress->getTelephone() ?? $billingAddress->getTelephone() ?? $customer->getTelephone() ?? null);
        $buttonConfig->setInitialFirstName($shippingAddress->getFirstname() ?? $billingAddress->getFirstname() ?? $customer->getFirstname() ?? null);
        $buttonConfig->setInitialLastName($shippingAddress->getLastname() ?? $billingAddress->getLastname() ?? $customer->getLastname() ?? null);
        $buttonConfig->setEnablePrePayTrigger(true);
        $buttonConfig->setEnablePreCheckoutTrigger(false);
    }

    /**
     * @param string $locale
     * @return string
     */
    protected function getCityCodesJson($locale): string
    {
        $locale = $locale ?? $this->localeResolver->getLocale();
        $cityCodes = $this->cityHelper->getCityCodes($locale);

        if (!$cityCodes) {
            return '';
        }

        return $this->jsonSerializer->serialize($cityCodes);
    }

    /**
     * @param string $locale
     * @return string
     */
    protected function getLimitedRegionCodesJson($locale): string
    {
        return $this->jsonSerializer->serialize(
            $this->getLimitedRegionsArray($locale)
        );
    }

    /**
     * @return array
     */
    public function getLimitedRegionsArray($locale): array
    {

        $limitedRegionCodes = [];
        $limitedRegions = $this->config->getLimitedRegions();
        $locale = $locale ?? $this->localeResolver->getLocale();
        $regionCollection = $this->regionCollectionFactory->create();
        $regionCollection->addFieldToFilter('main_table.region_id', ['in' => $limitedRegions]);
        foreach ($regionCollection->getItems() as $region) {
            $region->setLocale($locale);
            $regionName = $region->getName();
            $limitedRegionCodes[$region->getCountryId()][$region->getRegionId()] = $regionName;
        }

        return $limitedRegionCodes;
    }
}
