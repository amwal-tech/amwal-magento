<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Button;

use Amwal\Payments\Api\Data\AmwalButtonConfigInterface;
use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Model\Data\AmwalButtonConfig;
use Amwal\Payments\Model\ThirdParty\CityHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\Data\CartInterface;
use Amwal\Payments\ViewModel\ExpressCheckoutButton;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class GetCartButtonConfig extends GetConfig
{
    protected Json $jsonSerializer;
    protected CityHelper $cityHelper;
    protected StoreManagerInterface $storeManager;
    protected $amwalQuote;

    /**
     * @param RefIdDataInterface $refIdData
     * @param string|null $triggerContext
     * @param string|null $cartId
     * @param string|null $productId
     *
     * @return AmwalButtonConfigInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(
        RefIdDataInterface $refIdData,
        string $triggerContext = null,
        ?string $cartId = null,
        ?string $productId = null
    ): AmwalButtonConfigInterface   {
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
        } else {
            $quote = $this->checkoutSessionFactory->create()->getQuote();
            $cartId = $this->quoteIdMaskFactory->create()->load($quote->getId(), 'quote_id')->getMaskedId();
            if (!$cartId && $quote->getId()) {
                $cartId = $this->quoteIdMaskFactory->create()->setQuoteId($quote->getId())->save()->getMaskedId();
            }
        }
        $this->addGenericButtonConfig($buttonConfig, $refIdData, $quote, $customerSession, $initialAddress);
        if ($triggerContext === ExpressCheckoutButton::TRIGGER_CONTEXT_REGULAR_CHECKOUT) {
            $this->addRegularCheckoutButtonConfig($buttonConfig, $quote);
        }
        $buttonConfig->setCartId($cartId);
        $buttonConfig->setAmount($this->getAmount($quote, $buttonConfig, $productId));
        $buttonConfig->setDiscount($this->getDiscountAmount($quote, $buttonConfig, $productId));
        $buttonConfig->setTax($this->getTaxAmount($quote));
        $buttonConfig->setFees($this->getFeesAmount($quote));
        $buttonConfig->setId($this->getButtonId($cartId));
        $this->amwalQuote = $quote;

        $buttonConfig->setOrderContent($this->jsonSerializer->serialize($this->getOrderContent($quote)));

        if ($limitedCities = $this->getCityCodesJson()) {
            $buttonConfig->setAllowedAddressCities($limitedCities);
        }
        if ($limitedRegions = $this->getLimitedRegionCodesJson()) {
            $buttonConfig->setAllowedAddressStates($limitedRegions);
        }
        return $buttonConfig;
    }

    /**
     * @param CartInterface $quote
     * @param AmwalButtonConfigInterface $buttonConfig
     * @param string|null $productId
     *
     * @return float
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAmount(CartInterface $quote, AmwalButtonConfigInterface $buttonConfig, $productId = null): float
    {
        if ($productId && $buttonConfig->isShowDiscountRibbon()) {
            $product = $this->productRepository->getById($productId);
            return (float)$product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
        }

        return ((float)
            $quote->getGrandTotal() +
            $this->getDiscountAmount($quote, $buttonConfig, $productId) -
            $this->getTaxAmount($quote) -
            $this->getFeesAmount($quote)
        );
    }


    /**
     * @param CartInterface $quote
     * @param AmwalButtonConfigInterface $buttonConfig
     * @param string|null $productId
     *
     * @return float
     * @throws NoSuchEntityException
     */
    public function getDiscountAmount(CartInterface $quote, AmwalButtonConfigInterface $buttonConfig, $productId = null): float
    {
        $discountAmount = 0;
        if ($buttonConfig->isShowDiscountRibbon()) {
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
     * @param CartInterface $quote
     *
     * @return float
     */
    public function getTaxAmount(CartInterface $quote): float
    {
        return (float)$quote->getShippingAddress()->getTaxAmount();
    }

    /**
     * @param CartInterface $quote
     *
     * @return float
     */
    public function getFeesAmount(CartInterface $quote): float
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
     * @param CartInterface $quote
     *
     * @return void
     */
    public function addRegularCheckoutButtonConfig(AmwalButtonConfigInterface $buttonConfig, CartInterface $quote): void
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

    /**
     * @return CartInterface
     */
    public function getQuote()
    {
        return $this->amwalQuote;
    }

    /**
     * @param $quote
     * @return array
     */
    private function getOrderContent($quote): array
    {
        $orderContent = [];
        foreach ($quote->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $orderContent[] = [
                'id' => $item->getProductId(),
                'name' => $item->getName(),
                'quantity' => $item->getQty(),
                'total' => (float)$item->getRowTotalInclTax(),
                'url' => $item->getProduct()->getProductUrl(),
                'image' => $this->getProductImageUrl($item->getProduct())
            ];
        }
        return $orderContent;
    }

    /**
     * @param ProductInterface $product
     * @return string
     */
    private function getProductImageUrl(ProductInterface $product): string
    {
        $image = $product->getData('small_image') ?: $product->getData('thumbnail');
        if(!$image) {
            return '';
        }
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $image;
    }
}
