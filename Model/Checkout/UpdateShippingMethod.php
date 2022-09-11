<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Checkout;

use Amwal\Payments\Api\Data\AmwalAddressInterface;
use Amwal\Payments\Api\Data\AmwalOrderItemInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\ShippingMethodManagementInterface;
use Psr\Log\LoggerInterface;

class UpdateShippingMethod
{

    private CartRepositoryInterface $quoteRepository;
    private ShippingMethodManagementInterface $shippingMethodManagement;
    private LoggerInterface $logger;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        ShippingMethodManagementInterface $shippingMethodManagement,
        LoggerInterface $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->logger = $logger;
    }

    /**
     * @param CartInterface $quote
     * @param string $shippingMethod
     * @return bool
     */
    public function execute(CartInterface $quote, string $shippingMethod): bool
    {
        $quote->getShippingAddress()->setShippingMethod($shippingMethod);
        [$carrier, $method] = explode('_', $shippingMethod);

        try {
            $this->shippingMethodManagement->set($quote->getId(), $carrier, $method);
        } catch (CouldNotSaveException|InputException|NoSuchEntityException|StateException $e) {
            $this->logger->error(sprintf(
                'Unable to set shipping method for quote with ID %s. Error: %s',
                $quote->getId(),
                $e->getMessage()
            ));
            return false;
        }

        $quote->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates();
        $quote->collectTotals();
        $this->quoteRepository->save($quote);

        return true;
    }
}
