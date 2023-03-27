<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Checkout;

use Amwal\Payments\Model\Config;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

class CleanQuote
{
    private Config $config;
    private CheckoutSession $checkoutSession;
    private CartRepositoryInterface $cartRepository;
    private LoggerInterface $logger;

    /**
     * @param Config $config
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $cartRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $cartRepository,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->logger = $logger;
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

    private function logDebug(string $message, array $context = []): void
    {
        if (!$this->config->isDebugModeEnabled()) {
            return;
        }

        $this->logger->debug($message, $context);
    }
}
