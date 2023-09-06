<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Button;

use Amwal\Payments\Api\Data\AmwalButtonConfigInterface;
use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Model\Data\AmwalButtonConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ObjectManager;
use libphonenumber\PhoneNumberUtil;

class GetCartButtonConfig extends GetConfig
{
    /**
     * @param RefIdDataInterface $refIdData
     * @param string|null $triggerContext
     * @param int|null $quoteId
     * @return AmwalButtonConfigInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(RefIdDataInterface $refIdData, string $triggerContext = null, ?int $quoteId = null): AmwalButtonConfigInterface
    {
        /** @var AmwalButtonConfig $buttonConfig */
        $buttonConfig = $this->buttonConfigFactory->create();

        $this->addGenericButtonConfig($buttonConfig, $refIdData);

        $buttonConfig->setAmount($this->getAmount($quoteId));
        $buttonConfig->setId($this->getButtonId($quoteId));

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

        return (float) $quote->getGrandTotal();
    }

    /**
     * @param AmwalButtonConfigInterface $buttonConfig
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function addRegularCheckoutButtonConfig(AmwalButtonConfigInterface $buttonConfig, $quoteId): void
    {
        if($quoteId){
            $quote = $this->cartRepository->get($quoteId);
            $shippingAddress = $quote->getShippingAddress();
            $email = $shippingAddress->getEmail() ?? '';
        }else{
            $objectManager   = ObjectManager::getInstance();
            $checkoutSession = $objectManager->get(Session::class);
            $shippingAddress = $checkoutSession->getQuote()->getShippingAddress();
            $email = $checkoutSession->getQuote()->getCustomerEmail() ?? $checkoutSession->getQuote()->getBillingAddress()->getEmail();
        }

        $street          = $shippingAddress->getStreet()[0] ?? '';
        $formatedAddress = json_encode(['street1' => $street, 'city' => $shippingAddress->getCity(),'state' => $shippingAddress->getRegion(), 'country' => $shippingAddress->getCountryId(), 'postcode' => $shippingAddress->getPostcode()]);

        $buttonConfig->setAddressRequired(false);
        $buttonConfig->setInitialAddress($formatedAddress ?? null);
        $buttonConfig->setInitialEmail($email);
        $buttonConfig->setEnablePrePayTrigger(true);
        $buttonConfig->setEnablePreCheckoutTrigger(false);
    }
}
