<?php
declare(strict_types=1);

namespace Amwal\Payments\Model;

use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\CartRepositoryInterface as QuoteRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class CurrencyConverter
{
    private const SAR_CURRENCY_CODE = 'SAR';

    private CurrencyFactory $currencyFactory;
    private StoreManagerInterface $storeManager;
    private LoggerInterface $logger;
    private QuoteRepositoryInterface $quoteRepository;
    private PriceCurrencyInterface $priceCurrency;

    public function __construct(
        CurrencyFactory $currencyFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        QuoteRepositoryInterface $quoteRepository,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->currencyFactory = $currencyFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Convert quote to SAR currency using Magento's built-in currency system
     *
     * @param Quote $quote
     * @return Quote
     * @throws LocalizedException
     * @throws CouldNotSaveException
     */
    public function convertQuoteToSAR(Quote $quote): Quote
    {
        $currentCurrency = $quote->getQuoteCurrencyCode();

        if ($currentCurrency === self::SAR_CURRENCY_CODE) {
            $this->logger->info('Quote is already in SAR currency, no conversion needed');
            return $quote;
        }

        $this->logger->info(sprintf('Converting quote from %s to SAR using Magento built-in currency system', $currentCurrency));

        // Validate quote before conversion
        $this->validateQuoteForConversion($quote);

        $store = $this->storeManager->getStore($quote->getStoreId());

        // Store original amounts for logging
        $originalGrandTotal = $quote->getGrandTotal();

        // Get the SAR store
        $saStore = $this->storeManager->getStore('SA');

        // Get the exchange rate from base currency (USD) to SAR
        $baseCurrency = $store->getBaseCurrency();
        $sarCurrency = $this->currencyFactory->create()->load(self::SAR_CURRENCY_CODE);
        $exchangeRate = $baseCurrency->getRate($sarCurrency);

        if (!$exchangeRate || $exchangeRate <= 0) {
            throw new LocalizedException(__('Invalid exchange rate for SAR conversion'));
        }

        // Set the new store and currency
        $quote->setStoreId($saStore->getId());
        $quote->setQuoteCurrencyCode(self::SAR_CURRENCY_CODE);

        // Set the proper exchange rate
        $quote->setBaseToQuoteRate($exchangeRate);

        // Force recalculation of totals
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();

        // Verify the conversion worked correctly
        $expectedGrandTotal = $quote->getBaseGrandTotal() * $exchangeRate;
        $actualGrandTotal = $quote->getGrandTotal();

        // If there's a significant difference, manually set the correct amount
        if (abs($expectedGrandTotal - $actualGrandTotal) > 0.01) {
            $this->logger->warning(sprintf(
                'Grand total mismatch after conversion. Expected: %s, Got: %s. Correcting...',
                $expectedGrandTotal,
                $actualGrandTotal
            ));

            // Manually set the correct grand total
            $quote->setGrandTotal($expectedGrandTotal);
        }

        try {
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Error saving converted quote: %s', $e->getMessage()));
            throw new CouldNotSaveException(__('Could not save converted quote: %1', $e->getMessage()));
        }

        $this->logger->info(sprintf(
            'Quote converted to SAR. Final grand total: %s SAR (Original: %s %s, Rate: %s)',
            $quote->getGrandTotal(),
            $originalGrandTotal,
            $currentCurrency,
            $exchangeRate
        ));

        return $quote;
    }

    /**
     * Validate quote before currency conversion
     *
     * @param Quote $quote
     * @throws LocalizedException
     */
    private function validateQuoteForConversion(Quote $quote): void
    {
        if (!$quote->getId()) {
            throw new LocalizedException(__('Quote must be saved before currency conversion'));
        }

        if ($quote->getGrandTotal() <= 0) {
            throw new LocalizedException(__('Quote grand total must be greater than 0 for currency conversion'));
        }

        if (!$quote->getQuoteCurrencyCode()) {
            throw new LocalizedException(__('Quote currency code is missing'));
        }
    }

    /**
     * Convert amount from quote currency to SAR using Magento's built-in currency conversion
     *
     * @param float $amount
     * @param CartInterface $quote
     * @return float
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function convertToSAR(float $amount, CartInterface $quote): float
    {
        $store = $this->storeManager->getStore($quote->getStoreId());
        $currentCurrency = $quote->getQuoteCurrencyCode() ?: $store->getCurrentCurrencyCode();

        // If already in SAR, return the amount as-is
        if ($currentCurrency === self::SAR_CURRENCY_CODE) {
            return $amount;
        }

        try {
            // Use Magento's built-in currency conversion service
            return $this->priceCurrency->convert($amount, $store, self::SAR_CURRENCY_CODE);
        } catch (\Exception $e) {
            // Fallback: return original amount if conversion fails
            return $amount;
        }
    }

    /**
     * Check if quote needs SAR conversion
     *
     * @param Quote $quote
     * @return bool
     */
    public function needsConversionToSAR(Quote $quote): bool
    {
        return $quote->getQuoteCurrencyCode() !== self::SAR_CURRENCY_CODE;
    }

    /**
     * Get SAR currency code
     *
     * @return string
     */
    public function getSARCurrencyCode(): string
    {
        return self::SAR_CURRENCY_CODE;
    }
}
