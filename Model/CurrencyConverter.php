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
 * Handles all currency conversions for payment processing
 */
class CurrencyConverter
{
    private const DEFAULT_TARGET_CURRENCY = 'SAR';
    private const ROUNDING_PRECISION = 2;
    private const COMPARISON_TOLERANCE = 0.01; // 1 cent tolerance for float comparison

    // Supported currencies for Amwal Payments
    private const SUPPORTED_CURRENCIES = [
        'SAR', 'AED', 'USD', 'EUR', 'GBP', 'KWD', 'BHD', 'OMR', 'QAR', 'EGP', 'JOD'
    ];

    // Fallback exchange rates (SAR as base) for when rates are not configured
    private const FALLBACK_RATES = [
        'SAR' => 1.0,
        'AED' => 0.98,  // 1 AED = 1.02 SAR approximately
        'USD' => 3.75,  // 1 USD = 3.75 SAR approximately
        'EUR' => 4.1,   // 1 EUR = 4.1 SAR approximately
        'GBP' => 4.8,   // 1 GBP = 4.8 SAR approximately
        'KWD' => 12.3,  // 1 KWD = 12.3 SAR approximately
        'BHD' => 9.96,  // 1 BHD = 9.96 SAR approximately
        'OMR' => 9.74,  // 1 OMR = 9.74 SAR approximately
        'QAR' => 1.03,  // 1 QAR = 1.03 SAR approximately
        'EGP' => 0.12,  // 1 EGP = 0.12 SAR approximately
        'JOD' => 5.29   // 1 JOD = 5.29 SAR approximately
    ];

    public function __construct(
        private StoreManagerInterface $storeManager,
        private PriceCurrencyInterface $priceCurrency,
        private CurrencyFactory $currencyFactory,
        private LoggerInterface $logger
    ) {}

    /**
     * Check if currency is supported by Amwal
     *
     * @param string $currency
     * @return bool
     */
    public function isCurrencySupported(string $currency): bool
    {
        return in_array(strtoupper($currency), self::SUPPORTED_CURRENCIES);
    }

    /**
     * Get the best target currency for payment processing
     * Prefers to keep original currency if supported, otherwise converts to SAR
     *
     * @param Quote $quote
     * @return string
     */
    public function getPaymentCurrency(Quote $quote): string
    {
        $quoteCurrency = $quote->getQuoteCurrencyCode();

        // If quote currency is supported, use it directly
        if ($this->isCurrencySupported($quoteCurrency)) {
            $this->logger->debug(sprintf(
                'Using original quote currency %s for payment',
                $quoteCurrency
            ));
            return $quoteCurrency;
        }

        // Otherwise, default to SAR
        $this->logger->debug(sprintf(
            'Quote currency %s not supported, using SAR for payment',
            $quoteCurrency
        ));
        return self::DEFAULT_TARGET_CURRENCY;
    }

    /**
     * Convert amount between currencies
     *
     * @param float $amount Amount to convert
     * @param Quote $quote Quote object for context
     * @param string|null $targetCurrency Target currency code (null = auto-detect best currency)
     * @return float Converted amount
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function convertAmount(
        float $amount,
        Quote $quote,
        ?string $targetCurrency = null
    ): float {
        // Auto-detect best currency if not specified
        if ($targetCurrency === null) {
            $targetCurrency = $this->getPaymentCurrency($quote);
        }

        $store = $this->storeManager->getStore($quote->getStoreId());
        $currentCurrency = $quote->getQuoteCurrencyCode() ?: $store->getCurrentCurrencyCode();

        // No conversion needed if currencies match
        if ($currentCurrency === $targetCurrency) {
            return round($amount, self::ROUNDING_PRECISION);
        }

        // Handle zero amounts without conversion
        if ($amount == 0) {
            return 0.0;
        }

        try {
            $rate = $this->getExchangeRate($currentCurrency, $targetCurrency, $store->getId());

            if (!$rate || $rate <= 0) {
                // Try fallback rates
                $rate = $this->getFallbackRate($currentCurrency, $targetCurrency);

                if (!$rate || $rate <= 0) {
                    throw new LocalizedException(
                        __('No valid exchange rate from %1 to %2', $currentCurrency, $targetCurrency)
                    );
                }

                $this->logger->warning(sprintf(
                    'Using fallback exchange rate for %s to %s: %s',
                    $currentCurrency,
                    $targetCurrency,
                    $rate
                ));
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
     * Get fallback exchange rate when Magento rates are not configured
     *
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return float
     */
    private function getFallbackRate(string $fromCurrency, string $toCurrency): float
    {
        $fromCurrency = strtoupper($fromCurrency);
        $toCurrency = strtoupper($toCurrency);

        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        // Convert both to SAR first if not already
        $fromRate = self::FALLBACK_RATES[$fromCurrency] ?? 0;
        $toRate = self::FALLBACK_RATES[$toCurrency] ?? 0;

        if ($fromRate <= 0 || $toRate <= 0) {
            return 0;
        }

        // If converting to SAR
        if ($toCurrency === 'SAR') {
            return $fromRate;
        }

        // If converting from SAR
        if ($fromCurrency === 'SAR') {
            return 1 / $toRate;
        }

        // Cross-conversion through SAR
        return $fromRate / $toRate;
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
     * Get quote amounts in payment currency for processing
     * Keeps original currency if supported, otherwise converts to SAR
     *
     * @param Quote $quote
     * @param string|null $targetCurrency Override target currency (null = auto-detect)
     * @return array Array with amounts and metadata
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuoteAmountsForPayment(Quote $quote, ?string $targetCurrency = null): array
    {
        $currentCurrency = $quote->getQuoteCurrencyCode();
        $shippingAddress = $quote->getShippingAddress();

        // Auto-detect best payment currency if not specified
        if ($targetCurrency === null) {
            $targetCurrency = $this->getPaymentCurrency($quote);
        }

        // If already in target currency, return as-is
        if ($currentCurrency === $targetCurrency) {
            return [
                'grand_total' => round((float) $quote->getGrandTotal(), self::ROUNDING_PRECISION),
                'subtotal' => round((float) $quote->getSubtotal(), self::ROUNDING_PRECISION),
                'shipping_amount' => round((float) ($shippingAddress->getShippingAmount() ?? 0), self::ROUNDING_PRECISION),
                'tax_amount' => round((float) ($shippingAddress->getTaxAmount() ?? 0), self::ROUNDING_PRECISION),
                'discount_amount' => round((float) abs($shippingAddress->getDiscountAmount() ?? 0), self::ROUNDING_PRECISION),
                'currency' => $targetCurrency,
                'original_currency' => $currentCurrency,
                'original_amount' => round((float) $quote->getGrandTotal(), self::ROUNDING_PRECISION),
                'exchange_rate' => 1.0,
                'currency_supported' => true
            ];
        }

        try {
            // Convert amounts WITHOUT modifying the quote
            $grandTotalConverted = $this->convertAmount($quote->getGrandTotal(), $quote, $targetCurrency);

            return [
                'grand_total' => $grandTotalConverted,
                'subtotal' => $this->convertAmount($quote->getSubtotal(), $quote, $targetCurrency),
                'shipping_amount' => $this->convertAmount(
                    $shippingAddress->getShippingAmount() ?? 0,
                    $quote,
                    $targetCurrency
                ),
                'tax_amount' => $this->convertAmount(
                    $shippingAddress->getTaxAmount() ?? 0,
                    $quote,
                    $targetCurrency
                ),
                'discount_amount' => $this->convertAmount(
                    abs($shippingAddress->getDiscountAmount() ?? 0),
                    $quote,
                    $targetCurrency
                ),
                'currency' => $targetCurrency,
                'original_currency' => $currentCurrency,
                'original_amount' => round((float) $quote->getGrandTotal(), self::ROUNDING_PRECISION),
                'exchange_rate' => $quote->getGrandTotal() > 0
                    ? round($grandTotalConverted / $quote->getGrandTotal(), 4)
                    : 1.0,
                'currency_supported' => $this->isCurrencySupported($currentCurrency)
            ];

        } catch (\Exception $e) {
            $this->logger->error(sprintf(
                'Failed to get payment amounts for quote %s: %s',
                $quote->getId(),
                $e->getMessage()
            ));
            throw new LocalizedException(
                __('Could not convert quote amounts to %1: %2', $targetCurrency, $e->getMessage())
            );
        }
    }

    /**
     * Convert quote to specified target currency
     * WARNING: This modifies the quote permanently - use getQuoteAmountsForPayment for payment processing
     *
     * @param Quote $quote
     * @param string|null $targetCurrency
     * @return Quote Modified quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function convertQuoteToCurrency(
        Quote $quote,
        ?string $targetCurrency = null
    ): Quote {
        // Auto-detect if not specified
        if ($targetCurrency === null) {
            $targetCurrency = $this->getPaymentCurrency($quote);
        }

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

    /**
     * Check if quote needs conversion to target currency
     *
     * @param Quote $quote
     * @param string|null $targetCurrency
     * @return bool
     */
    public function needsConversion(Quote $quote, ?string $targetCurrency = null): bool
    {
        if ($targetCurrency === null) {
            $targetCurrency = $this->getPaymentCurrency($quote);
        }
        return $quote->getQuoteCurrencyCode() !== $targetCurrency;
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
            'is_original' => true,
            'is_supported' => $this->isCurrencySupported($quote->getQuoteCurrencyCode())
        ];
    }

    // ========== BACKWARD COMPATIBILITY METHODS ==========
    // These methods maintain compatibility with existing code

    /**
     * Get quote amounts in SAR for payment processing WITHOUT modifying the quote
     * @deprecated Use getQuoteAmountsForPayment() for better multi-currency support
     *
     * @param Quote $quote
     * @return array Array with converted amounts and metadata
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuoteAmountsInSAR(Quote $quote): array
    {
        return $this->getQuoteAmountsForPayment($quote, self::DEFAULT_TARGET_CURRENCY);
    }

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
     * @deprecated Use getQuoteAmountsForPayment() instead to avoid modifying the quote
     */
    public function convertQuoteToSAR(Quote $quote): Quote
    {
        return $this->convertQuoteToCurrency($quote, self::DEFAULT_TARGET_CURRENCY);
    }

    /**
     * Convert quote for payment processing (backward compatibility)
     * WARNING: This modifies the quote - consider using getQuoteAmountsForPayment() instead
     * @deprecated Use getQuoteAmountsForPayment() to avoid modifying the quote
     */
    public function convertQuoteForPayment(Quote $quote): Quote
    {
        $targetCurrency = $this->getPaymentCurrency($quote);

        $this->logger->debug(sprintf(
            'Converting quote to %s for payment (from %s)',
            $targetCurrency,
            $quote->getQuoteCurrencyCode()
        ));

        // Only convert if needed
        if ($this->needsConversion($quote, $targetCurrency)) {
            return $this->convertQuoteToCurrency($quote, $targetCurrency);
        }

        return $quote;
    }

    /**
     * Get payment amounts in SAR without modifying the quote
     * @deprecated Use getQuoteAmountsForPayment() for better multi-currency support
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
     * Detect the best target currency for the quote based on store configuration
     *
     * @param Quote $quote
     * @return string Currency code
     * @throws NoSuchEntityException
     */
    public function detectTargetCurrency(Quote $quote): string
    {
        // First check if current currency is supported
        $currentCurrency = $quote->getQuoteCurrencyCode();
        if ($this->isCurrencySupported($currentCurrency)) {
            return $currentCurrency;
        }

        try {
            $store = $this->storeManager->getStore($quote->getStoreId());
            $availableCurrencies = $store->getAvailableCurrencyCodes();

            // Check store's current currency
            $storeCurrency = $store->getCurrentCurrencyCode();
            if ($storeCurrency && $this->isCurrencySupported($storeCurrency) && in_array($storeCurrency, $availableCurrencies)) {
                return $storeCurrency;
            }

            // Check base currency
            $baseCurrency = $store->getBaseCurrencyCode();
            if ($baseCurrency && $this->isCurrencySupported($baseCurrency) && in_array($baseCurrency, $availableCurrencies)) {
                return $baseCurrency;
            }

        } catch (\Exception $e) {
            $this->logger->warning('Currency detection failed: ' . $e->getMessage());
        }

        // Default to SAR if no supported currency found
        return self::DEFAULT_TARGET_CURRENCY;
    }
}
