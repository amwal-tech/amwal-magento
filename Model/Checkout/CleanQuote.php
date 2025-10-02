<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Checkout;

use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\ErrorReporter;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

class CleanQuote extends AmwalCheckoutAction
{
    private CheckoutSession $checkoutSession;
    private CartRepositoryInterface $cartRepository;

    /**
     * CleanQuote constructor.
     *
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
     * Execute the quote cleaning process
     */
    public function execute(): void
    {
        try {
            $quote = $this->checkoutSession->getQuote();

            if ($quote && $quote->getId()) {
                $this->logDebug('Deactivating quote: ' . $quote->getId());

                $quote->setIsActive(false);
                $this->cartRepository->save($quote);
            }
            $this->checkoutSession->clearQuote();
            $this->checkoutSession->clearStorage();

            $this->logDebug('Quote cleaned successfully. New quote will be created automatically.');

        } catch (\Exception $e) {
            $this->logDebug('Error cleaning quote: ' . $e->getMessage());
        }
    }
}
