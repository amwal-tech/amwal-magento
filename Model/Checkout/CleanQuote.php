<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Checkout;

use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\ErrorReporter;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

class CleanQuote extends AmwalCheckoutAction
{
    private CheckoutSession $checkoutSession;
    private CartRepositoryInterface $cartRepository;

    /**
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $cartRepository
     * @param ErrorReporter $errorReporter
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $cartRepository,
        ErrorReporter $errorReporter,
        Config $config,
        LoggerInterface $logger
    ) {
        parent::__construct($errorReporter, $config, $logger);
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        try {
            $quote = $this->checkoutSession->getQuote();
        } catch (NoSuchEntityException|LocalizedException $e) {
            $this->logDebug('No existing quote found, skipping cleanup.');
            return;
        }

        if (!$quote) {
            $this->logDebug('No existing quote found, skipping cleanup.');
            return;
        }

        $this->logDebug('Starting Quote cleanup.');
        $quote->removeAllItems();
        $this->cartRepository->save($quote);
        $this->logDebug('Quote cleanup completed.');
    }
}
