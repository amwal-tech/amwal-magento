<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Checkout;

use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\ErrorReporter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface as QuoteRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Psr\Log\LoggerInterface;

class CleanQuote extends AmwalCheckoutAction
{
    private CartRepositoryInterface $cartRepository;
    private MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId;
    private QuoteRepositoryInterface $quoteRepository;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param QuoteRepositoryInterface $quoteRepository
     * @param ErrorReporter $errorReporter
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        QuoteRepositoryInterface $quoteRepository,
        ErrorReporter $errorReporter,
        Config $config,
        LoggerInterface $logger
    ) {
        parent::__construct($errorReporter, $config, $logger);
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param string|null $cartId
     * @return void
     */
    public function execute(
        ?string $cartId = null
    ) : void
    {
        try {
            $quoteId = is_numeric($cartId) ? $cartId : $this->maskedQuoteIdToQuoteId->execute($cartId);
            $quote = $this->quoteRepository->get($quoteId);
            $this->logDebug('Found existing quote.', ['quote_id' => $quote->getId(), 'customer_id' => $quote->getCustomerId(), 'items_count' => $quote->getItemsCount()]);
        } catch (NoSuchEntityException|LocalizedException $e) {
            $this->logDebug('No existing quote found, skipping cleanup.');
            return;
        }

        if (!$quote) {
            $this->logDebug('No existing quote found, skipping cleanup.');
            return;
        }

        $this->logDebug('Starting Quote cleanup.');
        $quote->setData(self::IS_AMWAL_API_CALL, true);
        $quote->removeAllItems();
        $this->cartRepository->save($quote);
        $this->logDebug('Quote cleanup completed.');
    }
}
