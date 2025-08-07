<?php
declare(strict_types=1);

namespace Amwal\Payments\Model;

use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Currency Converter for Amwal Payments
 * Handles all currency conversions for payment processing, primarily to SAR
 */
class CurrencyConverter
{
    private const DEFAULT_TARGET_CURRENCY = 'SAR';
    private const ROUNDING_PRECISION = 2;
    private const COMPARISON_TOLERANCE = 0.01; // 1 cent tolerance for float comparison

    public function __construct(
        private StoreManagerInterface $storeManager,
        private PriceCurrencyInterface $priceCurrency,
        private CurrencyFactory $currencyFactory,
        private LoggerInterface $logger
    ) {}

    /**
     * Convert amount between currencies
     *
     * @param float $amount Amount to convert
     * @param Quote $quote Quote object for context
     * @param string $targetCurrency Target currency code
     * @return float Converted amount
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function convertAmount(
        float $amount,
        Quote $quote,
        string $targetCurrency = self::DEFAULT_TARGET_CURRENCY
    ): float {
        $store = $this->storeManager->getStore($quote->getStoreId());
        $currentCurrency = $quote->getQuoteCurrencyCode() ?: $store->getCurrentCurrencyCode();

        // No conversion needed if currencies match
        if ($currentCurrency === $targetCurrency) {
            return $amount;
        }

        // Handle zero amounts without conversion
        if ($amount == 0) {
            return 0.0;
        }

        try {
            $rate = $this->getExchangeRate($currentCurrency, $targetCurrency, $store->getId());

            if (!$rate || $rate <= 0) {
                throw new LocalizedException(
                    __('No valid exchange rate from %1 to %2', $currentCurrency, $targetCurrency)
                );
            }

            $convertedAmount = $amount * $rate;

            $this->logger->debug(sprintf(
                'Currency conversion: %s %s -> %s %s (rate: %s)',
                $amount,
                $currentCurrency,
                $convertedAmount,
                $targetCurrency,
                $rate
            ));

            return round($convertedAmount, self::ROUNDING_PRECISION);

        } catch (\Exception $e) {
            $this->logger->error(sprintf(
                'Currency conversion failed from %s to %s: %s',
                $currentCurrency,
                $targetCurrency,
                $e->getMessage()
            ));
            throw new LocalizedException(
                __('Could not convert amount from %1 to %2: %3', $currentCurrency, $targetCurrency, $e->getMessage())
            );
        }
    }

    /**
     * Get exchange rate between two currencies
     *
     * @param string $fromCurrency Source currency
     * @param string $toCurrency Target currency
     * @param string|int|null $storeId Store ID for context
     * @return float Exchange rate
     * @throws NoSuchEntityException
     */
    private function getExchangeRate(string $fromCurrency, string $toCurrency, string|int|null $storeId = null): float
    {
        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        $store = $this->storeManager->getStore($storeId);
        $baseCurrency = $store->getBaseCurrencyCode();

        // Try direct conversion first
        $currency = $this->currencyFactory->create()->load($fromCurrency);
        $rate = $currency->getAnyRate($toCurrency);

        if ($rate && $rate > 0) {
            return (float) $rate;
        }

        // If no direct rate, try cross-conversion through base currency
        if ($fromCurrency !== $baseCurrency && $toCurrency !== $baseCurrency) {
            $fromToBase = $this->getExchangeRate($fromCurrency, $baseCurrency, $storeId);
            $baseToTarget = $this->getExchangeRate($baseCurrency, $toCurrency, $storeId);

            if ($fromToBase > 0 && $baseToTarget > 0) {
                return $fromToBase * $baseToTarget;
            }
        }

        // Try inverse rate as last resort
        $inverseCurrency = $this->currencyFactory->create()->load($toCurrency);
        $inverseRate = $inverseCurrency->getAnyRate($fromCurrency);

        if ($inverseRate && $inverseRate > 0) {
            return 1 / $inverseRate;
        }

        return 0;
    }

    /**
     * Get quote amounts in SAR for payment processing WITHOUT modifying the quote
     *
     * @param Quote $quote
     * @return array Array with converted amounts and metadata
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuoteAmountsInSAR(Quote $quote): array
    {
        $currentCurrency = $quote->getQuoteCurrencyCode();
        $shippingAddress = $quote->getShippingAddress();

        // If already in SAR, return as-is
        if ($currentCurrency === self::DEFAULT_TARGET_CURRENCY) {
            return [
                'grand_total' => round((float) $quote->getGrandTotal(), self::ROUNDING_PRECISION),
                'subtotal' => round((float) $quote->getSubtotal(), self::ROUNDING_PRECISION),
                'shipping_amount' => round((float) ($shippingAddress->getShippingAmount() ?? 0), self::ROUNDING_PRECISION),
                'tax_amount' => round((float) ($shippingAddress->getTaxAmount() ?? 0), self::ROUNDING_PRECISION),
                'discount_amount' => round((float) abs($shippingAddress->getDiscountAmount() ?? 0), self::ROUNDING_PRECISION),
                'currency' => self::DEFAULT_TARGET_CURRENCY,
                'original_currency' => $currentCurrency,
                'original_amount' => round((float) $quote->getGrandTotal(), self::ROUNDING_PRECISION),
                'exchange_rate' => 1.0
            ];
        }

        try {
            // Convert amounts WITHOUT modifying the quote
            $grandTotalSAR = $this->convertAmount($quote->getGrandTotal(), $quote, self::DEFAULT_TARGET_CURRENCY);

            return [
                'grand_total' => $grandTotalSAR,
                'subtotal' => $this->convertAmount($quote->getSubtotal(), $quote, self::DEFAULT_TARGET_CURRENCY),
                'shipping_amount' => $this->convertAmount(
                    $shippingAddress->getShippingAmount() ?? 0,
                    $quote,
                    self::DEFAULT_TARGET_CURRENCY
                ),
                'tax_amount' => $this->convertAmount(
                    $shippingAddress->getTaxAmount() ?? 0,
                    $quote,
                    self::DEFAULT_TARGET_CURRENCY
                ),
                'discount_amount' => $this->convertAmount(
                    abs($shippingAddress->getDiscountAmount() ?? 0),
                    $quote,
                    self::DEFAULT_TARGET_CURRENCY
                ),
                'currency' => self::DEFAULT_TARGET_CURRENCY,
                'original_currency' => $currentCurrency,
                'original_amount' => round((float) $quote->getGrandTotal(), self::ROUNDING_PRECISION),
                'exchange_rate' => $quote->getGrandTotal() > 0
                    ? round($grandTotalSAR / $quote->getGrandTotal(), 4)
                    : 1.0
            ];

        } catch (\Exception $e) {
            $this->logger->error(sprintf(
                'Failed to get SAR amounts for quote %s: %s',
                $quote->getId(),
                $e->getMessage()
            ));
            throw new LocalizedException(
                __('Could not convert quote amounts to SAR: %1', $e->getMessage())
            );
        }
    }

    /**
     * Convert quote to specified target currency
     * WARNING: This modifies the quote permanently - use getQuoteAmountsInSAR for payment processing
     *
     * @param Quote $quote
     * @param string $targetCurrency
     * @return Quote Modified quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function convertQuoteToCurrency(
        Quote $quote,
        string $targetCurrency = self::DEFAULT_TARGET_CURRENCY
    ): Quote {
        if ($quote->getQuoteCurrencyCode() === $targetCurrency) {
            return $quote;
        }

        $store = $this->storeManager->getStore($quote->getStoreId());

        if (!in_array($targetCurrency, $store->getAvailableCurrencyCodes())) {
            throw new LocalizedException(
                __('Currency %1 is not available for store %2', $targetCurrency, $store->getCode())
            );
        }

        $this->logger->warning(sprintf(
            'Permanently converting quote %s from %s to %s',
            $quote->getId(),
            $quote->getQuoteCurrencyCode(),
            $targetCurrency
        ));

        $store->setCurrentCurrencyCode($targetCurrency);
        $quote->setQuoteCurrencyCode($targetCurrency)
            ->setTotalsCollectedFlag(false)
            ->collectTotals();

        return $quote;
    }

    /**
     * Check if quote needs conversion to target currency
     *
     * @param Quote $quote
     * @param string $targetCurrency
     * @return bool
     */
    public function needsConversion(Quote $quote, string $targetCurrency = self::DEFAULT_TARGET_CURRENCY): bool
    {
        return $quote->getQuoteCurrencyCode() !== $targetCurrency;
    }

    /**
     * Detect the best target currency for the quote based on store configuration
     *
     * @param Quote $quote
     * @return string Currency code
     * @throws NoSuchEntityException
     */
    public function detectTargetCurrency(Quote $quote): string
    {
        try {
            $store = $this->storeManager->getStore($quote->getStoreId());
            $availableCurrencies = $store->getAvailableCurrencyCodes();

            // Priority: Current store currency > Base currency > SAR
            $currentCurrency = $store->getCurrentCurrencyCode();
            if ($currentCurrency && in_array($currentCurrency, $availableCurrencies)) {
                return $currentCurrency;
            }

            $baseCurrency = $store->getBaseCurrencyCode();
            if ($baseCurrency && in_array($baseCurrency, $availableCurrencies)) {
                return $baseCurrency;
            }

        } catch (\Exception $e) {
            $this->logger->warning('Currency detection failed: ' . $e->getMessage());
        }

        return self::DEFAULT_TARGET_CURRENCY;
    }

    /**
     * Get available currencies for a store
     *
     * @param string|int|null $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAvailableCurrencies(string|int|null $storeId = null): array
    {
        $store = $this->storeManager->getStore($storeId);
        return $store->getAvailableCurrencyCodes();
    }

    /**
     * Get amount in original currency for validation (no conversion)
     *
     * @param Quote $quote
     * @return array
     */
    public function getOriginalQuoteAmount(Quote $quote): array
    {
        $shippingAddress = $quote->getShippingAddress();

        return [
            'grand_total' => round((float) $quote->getGrandTotal(), self::ROUNDING_PRECISION),
            'subtotal' => round((float) $quote->getSubtotal(), self::ROUNDING_PRECISION),
            'shipping_amount' => round((float) ($shippingAddress->getShippingAmount() ?? 0), self::ROUNDING_PRECISION),
            'tax_amount' => round((float) ($shippingAddress->getTaxAmount() ?? 0), self::ROUNDING_PRECISION),
            'discount_amount' => round((float) abs($shippingAddress->getDiscountAmount() ?? 0), self::ROUNDING_PRECISION),
            'currency' => $quote->getQuoteCurrencyCode(),
            'is_original' => true
        ];
    }

    /**
     * Validate if amount matches quote total (considering currency)
     *
     * @param float $amount Amount to validate
     * @param Quote $quote Quote to validate against
     * @param string|null $amountCurrency Currency of the amount (null = quote currency)
     * @return bool
     */
    public function validateAmount(float $amount, Quote $quote, ?string $amountCurrency = null): bool
    {
        $quoteCurrency = $quote->getQuoteCurrencyCode();
        $quoteTotal = round((float) $quote->getGrandTotal(), self::ROUNDING_PRECISION);

        // If no currency specified, assume it's in quote currency
        if (!$amountCurrency) {
            $amountCurrency = $quoteCurrency;
        }

        // If currencies match, direct comparison
        if ($amountCurrency === $quoteCurrency) {
            return abs($amount - $quoteTotal) < self::COMPARISON_TOLERANCE;
        }

        // If currencies don't match, convert and compare
        try {
            $convertedAmount = $this->convertAmount($quoteTotal, $quote, $amountCurrency);
            return abs($amount - $convertedAmount) < self::COMPARISON_TOLERANCE;
        } catch (\Exception $e) {
            $this->logger->error('Amount validation failed: ' . $e->getMessage());
            return false;
        }
    }

    // ========== BACKWARD COMPATIBILITY METHODS ==========
    // These methods maintain compatibility with existing code
    // Consider them deprecated - use the main methods above

    /**
     * Convert to SAR (backward compatibility)
     * @deprecated Use convertAmount() instead
     */
    public function convertToSAR(float $amount, Quote $quote): float
    {
        return $this->convertAmount($amount, $quote, self::DEFAULT_TARGET_CURRENCY);
    }

    /**
     * Convert quote to SAR permanently (backward compatibility - avoid using)
     * @deprecated Use getQuoteAmountsInSAR() instead to avoid modifying the quote
     */
    public function convertQuoteToSAR(Quote $quote): Quote
    {
        return $this->convertQuoteToCurrency($quote, self::DEFAULT_TARGET_CURRENCY);
    }

    /**
     * Convert quote for payment processing (backward compatibility)
     * WARNING: This modifies the quote to SAR - consider using getQuoteAmountsInSAR() instead
     * @deprecated Use getQuoteAmountsInSAR() to avoid modifying the quote
     */
    public function convertQuoteForPayment(Quote $quote): Quote
    {
        $this->logger->debug(sprintf(
            'Converting quote to SAR for payment (from %s)',
            $quote->getQuoteCurrencyCode()
        ));

        // For backward compatibility, return the converted Quote object
        return $this->convertQuoteToCurrency($quote, self::DEFAULT_TARGET_CURRENCY);
    }

    /**
     * Get payment amounts in SAR without modifying the quote
     * This is the recommended method for payment processing
     *
     * @param Quote $quote
     * @return array Array with SAR amounts and original currency info
     */
    public function getPaymentAmountsInSAR(Quote $quote): array
    {
        return $this->getQuoteAmountsInSAR($quote);
    }

    /**
     * Check if needs SAR conversion (backward compatibility)
     * @deprecated Use needsConversion() instead
     */
    public function needsConversionToSAR(Quote $quote): bool
    {
        return $this->needsConversion($quote, self::DEFAULT_TARGET_CURRENCY);
    }

    /**
     * Get SAR currency code (backward compatibility)
     * @deprecated Use the constant directly
     */
    public function getSARCurrencyCode(): string
    {
        return self::DEFAULT_TARGET_CURRENCY;
    }

    /**
     * Convert quote for display purposes
     * @deprecated Use convertQuoteToCurrency() with detected currency
     */
    public function convertQuoteForDisplay(Quote $quote, ?string $preferredCurrency = null): Quote
    {
        $targetCurrency = $preferredCurrency ?: $this->detectTargetCurrency($quote);
        return $this->convertQuoteToCurrency($quote, $targetCurrency);
    }

    /**
     * Convert quote to dynamically detected currency
     * @deprecated Use convertQuoteToCurrency() with detectTargetCurrency()
     */
    public function convertQuoteToDynamicCurrency(Quote $quote): Quote
    {
        $targetCurrency = $this->detectTargetCurrency($quote);
        return $this->convertQuoteToCurrency($quote, $targetCurrency);
    }
}
