<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Button;

use Amwal\Payments\Api\Data\AmwalButtonConfigInterface;
use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Model\Data\AmwalButtonConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class GetCartButtonConfig extends GetConfig
{
    /**
     * @param RefIdDataInterface $refIdData
     * @param int|null $quoteId
     * @return AmwalButtonConfigInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(RefIdDataInterface $refIdData, ?int $quoteId = null): AmwalButtonConfigInterface
    {
        /** @var AmwalButtonConfig $buttonConfig */
        $buttonConfig = $this->buttonConfigFactory->create();

        $this->addGenericButtonConfig($buttonConfig, $refIdData);

        $buttonConfig->setAmount($this->getAmount($quoteId));
        $buttonConfig->setId($this->getButtonId($quoteId));

        if ($triggerContext == 'regular-checkout') {
            $this->addRegularCheckoutButtonConfig($buttonConfig);
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

        return (float) $quote->getGrandTotal();
    }

    /**
     * @param AmwalButtonConfigInterface $buttonConfig
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function addRegularCheckoutButtonConfig(AmwalButtonConfigInterface $buttonConfig): void
    {
        $objectManager = ObjectManager::getInstance();
        $customerSession = $objectManager->get(Session::class);
        $customer = $objectManager->get(Customer::class);
        $customerSession->getCustomer();
        $customer->load($customerSession->getCustomer()->getId());

        if ($customer->getDefaultShippingAddress()) {
            $address = $customer->getDefaultShippingAddress();
            $phone_number = $address->getTelephone();
            $formatedAddress = json_encode(['street1' => $address->getStreet(), 'city' => $address->getCity(),'state' => $address->getRegion(), 'country' => $address->getCountryId(), 'postcode' => $address->getPostcode()]);
        }

        $buttonConfig->setAddressRequired(false);
        $buttonConfig->setInitialAddress($formatedAddress ?? null);
        $buttonConfig->setInitialEmail($customer->getEmail());
        $buttonConfig->setInitialPhone($phone_number ?? null);
        $buttonConfig->setEnablePrePayTrigger(true);
        $buttonConfig->setEnablePreCheckoutTrigger(false);
    }
}
