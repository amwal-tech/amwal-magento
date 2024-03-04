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
use Magento\Quote\Api\Data\CartInterface;
use Amwal\Payments\ViewModel\ExpressCheckoutButton;

class GetCartButtonConfig extends GetConfig
{
    protected Json $jsonSerializer;
    protected CityHelper $cityHelper;

    /**
     * @param RefIdDataInterface $refIdData
     * @param string|null $triggerContext
     * @return AmwalButtonConfigInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(
            RefIdDataInterface $refIdData,
            string $triggerContext = null,
            ?string $cartId = null,
            ?string $productId = null
    ): AmwalButtonConfigInterface
    {
        /** @var AmwalButtonConfig $buttonConfig */
        $buttonConfig = $this->buttonConfigFactory->create();
        $customerSession = $this->customerSessionFactory->create();
        $initialAddress = $this->amwalAddressFactory->create();
        $quote = null;

        if ($cartId) {
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            if ($quoteIdMask) {
                $quoteId = (int) $quoteIdMask->getQuoteId();
                $quote = $this->cartRepository->get($quoteId);
            }
        }else{
            $quote = $this->checkoutSessionFactory->create()->getQuote();
            $cartId = $this->quoteIdMaskFactory->create()->load($quote->getId(), 'quote_id')->getMaskedId();
            if (!$cartId && $quote->getId()) {
                $cartId = $this->quoteIdMaskFactory->create()->setQuoteId($quote->getId())->save()->getMaskedId();
            }
            $buttonConfig->setCartId($cartId);
        }
        $this->addGenericButtonConfig($buttonConfig, $refIdData, $quote, $customerSession, $initialAddress);
        if ($triggerContext ===  ExpressCheckoutButton::TRIGGER_CONTEXT_REGULAR_CHECKOUT) {
            $this->addRegularCheckoutButtonConfig($buttonConfig, $quote);
        }

        $buttonConfig->setAmount($this->getAmount($quote, $buttonConfig, $productId, $triggerContext));
        $buttonConfig->setDiscount($this->getDiscountAmount($quote, $buttonConfig, $productId));
        $buttonConfig->setTax($this->getTaxAmount($quote, $buttonConfig, $productId));
        $buttonConfig->setFees($this->getFeesAmount($quote, $buttonConfig, $productId));
        $buttonConfig->setId($this->getButtonId($cartId));

        if ($limitedCities = $this->getCityCodesJson()) {
            $buttonConfig->setAllowedAddressCities($limitedCities);
        }
        if ($limitedRegions = $this->getLimitedRegionCodesJson()) {
            $buttonConfig->setAllowedAddressStates($limitedRegions);
        }
        return $buttonConfig;
    }

    /**
     * @param int|null $quoteId
     * @param AmwalButtonConfigInterface $buttonConfig
     * @param string|null $productId
     * @param string|null $triggerContext
     * @return float
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAmount($quote, AmwalButtonConfigInterface $buttonConfig, $productId = null, $triggerContext = null): float
    {
        if ($buttonConfig->getShowDiscountRibbon()) {
            if ($productId) {
                $product = $this->productRepository->getById($productId);
                return (float)$product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
            }
        }
        return ((float)
            $quote->getGrandTotal() +
            $this->getDiscountAmount($quote, $buttonConfig, $productId) -
            $this->getTaxAmount($quote, $buttonConfig, $productId) -
            $this->getFeesAmount($quote, $buttonConfig, $productId)
        );
    }


    /**
     * @param int|null $quoteId
     * @param AmwalButtonConfigInterface $buttonConfig
     * @param string|null $productId
     * @return float
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getDiscountAmount($quote, AmwalButtonConfigInterface $buttonConfig, $productId = null): float
    {
        $discountAmount = 0;
        if ($buttonConfig->getShowDiscountRibbon()) {
            if ($productId) {
                $product = $this->productRepository->getById($productId);
                $priceInfo = $product->getPriceInfo();
                $discountAmount += $priceInfo->getPrice('regular_price')->getAmount()->getValue() - $priceInfo->getPrice('final_price')->getAmount()->getValue();
            } else {
                foreach ($quote->getAllVisibleItems() as $item) {
                    $product = $this->productRepository->get($item->getSku());
                    $priceInfo = $product->getPriceInfo();
                    $price = $priceInfo->getPrice('regular_price')->getAmount()->getValue() - $priceInfo->getPrice('final_price')->getAmount()->getValue();
                    $discountAmount += $price * $item->getQty();
                }
            }
            $discountAmount += abs((float)$quote->getShippingAddress()->getDiscountAmount());
        }
        return $discountAmount;
    }

    /**
     * @param int|null $quoteId
     * @param AmwalButtonConfigInterface $buttonConfig
     * @param string|null $productId
     * @return float
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getTaxAmount($quote, AmwalButtonConfigInterface $buttonConfig, $productId = null): float
    {
        return (float)$quote->getShippingAddress()->getTaxAmount();
    }

    /**
     * @param int|null $quoteId
     * @param AmwalButtonConfigInterface $buttonConfig
     * @param string|null $productId
     * @return float
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getFeesAmount($quote, AmwalButtonConfigInterface $buttonConfig, $productId = null): float
    {
        $extraFee = 0;
        $totals = $quote->getTotals();
        if (isset($totals['amasty_extrafee'])) {
            $extraFee = $totals['amasty_extrafee']->getValueInclTax();
        }
        return $extraFee;
    }

    /**
     * @param AmwalButtonConfigInterface $buttonConfig
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function addRegularCheckoutButtonConfig(AmwalButtonConfigInterface $buttonConfig, $quote): void
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
        $buttonConfig->setEnablePreCheckoutTrigger($this->config->isPreCheckoutTriggerEnabled());
        $buttonConfig->setShowDiscountRibbon(false);
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
