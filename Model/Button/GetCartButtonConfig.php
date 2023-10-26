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
    /**
     * @param RefIdDataInterface $refIdData
     * @param string|null $triggerContext
     * @param int|null $quoteId
     * @return AmwalButtonConfigInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(
            RefIdDataInterface $refIdData,
            string $triggerContext = null,
            ?int $quoteId = null,
            ?string $cartId = null): AmwalButtonConfigInterface
    {
        /** @var AmwalButtonConfig $buttonConfig */
        $buttonConfig = $this->buttonConfigFactory->create();

        if ($cartId) {
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            if ($quoteIdMask) {
                $quoteId = (int) $quoteIdMask->getQuoteId();
            }
        }else{
            $quote = $this->checkoutSessionFactory->create()->getQuote();
            $maskId = $this->quoteIdMaskFactory->create()->load($quote->getId(), 'quote_id')->getMaskedId();
            $buttonConfig->setMaskId($maskId);
        }
        if ($quoteId) {
            $quote = $this->cartRepository->get($quoteId);
        } else {
            $quote = $this->checkoutSessionFactory->create()->getQuote();
        }
        $this->addGenericButtonConfig($buttonConfig, $refIdData, $quote);

        $buttonConfig->setAmount($this->getAmount($quote));
        $buttonConfig->setId($this->getButtonId($quoteId));

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
     * @param int|null $quoteId
     * @return float
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getAmount($quote): float
    {
        return (float)$quote->getGrandTotal();
    }

    /**
     * @param AmwalButtonConfigInterface $buttonConfig
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function addRegularCheckoutButtonConfig(AmwalButtonConfigInterface $buttonConfig, $quote): void
    {
        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();
        $customer = $quote->getCustomer();

        $street = $shippingAddress->getStreet()[0] ?? '';
        $street2 = $shippingAddress->getStreet()[1] ?? '';
        $formatedAddress = json_encode(['street1' => $street, 'street2' => $street2, 'city' => $shippingAddress->getCity(), 'state' => $shippingAddress->getRegion() ?? $shippingAddress->getCity(), 'country' => $shippingAddress->getCountryId(), 'postcode' => $shippingAddress->getPostcode()]);

        $buttonConfig->setAddressRequired(false);
        $buttonConfig->setInitialAddress($formatedAddress ?? null);
        $buttonConfig->setInitialEmail($shippingAddress->getEmail() ?? $billingAddress->getEmail() ?? $customer->getEmail() ?? null);
        $buttonConfig->setInitialPhone($shippingAddress->getTelephone() ?? $billingAddress->getTelephone() ?? null);
        $buttonConfig->setInitialFirstName($shippingAddress->getFirstname() ?? $billingAddress->getFirstname() ?? $customer->getFirstname() ?? null);
        $buttonConfig->setInitialLastName($shippingAddress->getLastname() ?? $billingAddress->getLastname() ?? $customer->getLastname() ?? null);
        $buttonConfig->setEnablePrePayTrigger(true);
        $buttonConfig->setEnablePreCheckoutTrigger(false);
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
