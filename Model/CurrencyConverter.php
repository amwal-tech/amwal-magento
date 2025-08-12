<?php
declare(strict_types=1);

namespace Amwal\Payments\Model;

use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class CurrencyConverter
{
    private const DEFAULT_TARGET_CURRENCY = 'SAR';

    /**
     * @var array<string, float> Caches conversion rates for the duration of a single request.
     */
    private array $conversionRates = [];

    private StoreManagerInterface $storeManager;
    private PriceCurrencyInterface $priceCurrency;
    private CurrencyFactory $currencyFactory;
    private LoggerInterface $logger;

    public function __construct(
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        CurrencyFactory $currencyFactory,
        LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->currencyFactory = $currencyFactory;
        $this->logger = $logger;
    }
    
    /**
     * Convert the given amount to the target currency.
     *
     * @param float $amount The amount to convert.
     * @param Quote|null $quote The quote object, if available.
     * @param string|null $targetCurrency The target currency code. Defaults to SAR.
     * @param bool $roundResult Whether to truncate the result to 2 decimal places. Defaults to true.
     * @return array An array containing the converted amount and the target currency.
     * @throws LocalizedException|NoSuchEntityException
     */
    public function convertAmount(
        float $amount,
        Quote $quote = null,
        ?string $targetCurrency = null,
        bool $roundResult = true
    ): array {
        try {
            $targetCurrency = $targetCurrency ?: self::DEFAULT_TARGET_CURRENCY;
            $store = $this->getStore($quote);
            $currentCurrencyCode = $store->getCurrentCurrency()->getCode();

            if ($currentCurrencyCode === $targetCurrency) {
                $finalAmount = $roundResult ? $this->priceCurrency->roundPrice($amount) : $amount;
                return [
                    'amount' => $finalAmount,
                    'currency' => $targetCurrency,
                    'original_amount' => $finalAmount,
                    'original_currency' => $currentCurrencyCode
                ];
            }

            $rate = $this->getCombinedConversionRate($currentCurrencyCode, $targetCurrency, $store);
            $convertedAmount = $amount * $rate;

            if ($roundResult) {
                $convertedAmount = $this->priceCurrency->roundPrice($convertedAmount);
            }

            return [
                'amount' => $convertedAmount,
                'currency' => $targetCurrency,
                'original_amount' => $roundResult ? $this->priceCurrency->roundPrice($amount) : $amount,
                'original_currency' => $currentCurrencyCode
            ];
        } catch (\Exception $e) {
            $this->logger->error('Currency conversion failed in convertAmount', [
                'amount' => $amount, 'target_currency' => $targetCurrency, 'exception' => $e
            ]);
            throw new LocalizedException(__('Unable to convert currency: %1', $e->getMessage()));
        }
    }

    /**
     * Convert amount to SAR and return only the converted amount as float.
     *
     * @param float|string|null $amount The amount to convert.
     * @param Quote|null $quote The quote object, if available.
     * @param bool $roundResult Whether to truncate the result. Defaults to true.
     * @return float The converted amount in SAR.
     * @throws LocalizedException|NoSuchEntityException
     */
    public function convertToSAR($amount, Quote $quote = null, bool $roundResult = true): float
    {
        if ($amount === null || $amount === '' || $amount == 0) {
            return 0.0;
        }

        $amount = (float) $amount;

        $result = $this->convertAmount($amount, $quote, self::DEFAULT_TARGET_CURRENCY, $roundResult);
        return $result['amount'];
    }

    /**
     * Get simplified quote amounts converted to SAR.
     * This is the final, production-ready version that handles both native and foreign currencies correctly.
     *
     * @param Quote $quote
     * @param bool $roundResult Whether to round the results. Defaults to true.
     * @return array Array containing main converted amounts in SAR
     * @throws LocalizedException|NoSuchEntityException
     */
    public function getMainAmountsInSAR(Quote $quote, bool $roundResult = true): array
    {
        // Step 1: Ensure Magento's internal calculations are complete and up-to-date.
        $quote->collectTotals();

        $address = $quote->getShippingAddress();
        if (!$address) {
            // Return zeroed array if the quote is incomplete.
            return [
                'subtotal' => 0.0, 'tax_amount' => 0.0, 'shipping_amount' => 0.0,
                'discount_amount' => 0.0, 'grand_total' => 0.0
            ];
        }

        $currentCurrencyCode = $quote->getStore()->getCurrentCurrency()->getCode();
        $targetCurrency = self::DEFAULT_TARGET_CURRENCY;

        // Step 2: Check if conversion is necessary.
        // THIS IS THE KEY to getting a perfect match with your expected native SAR output.
        if ($currentCurrencyCode === $targetCurrency) {

            // --- SCENARIO A: NATIVE SAR ---
            // No conversion needed. Use the exact values from Magento to guarantee a perfect match.
            $subtotal = $address->getSubtotal();
            $taxAmount = $address->getTaxAmount();
            $shippingAmount = $address->getShippingAmount();
            $discountAmount = abs($address->getDiscountAmount() ?? 0.0);
            $grandTotal = $quote->getGrandTotal();

        } else {

            // --- SCENARIO B: FOREIGN CURRENCY ---
            // A conversion is needed. Use the most reliable method: direct conversion of Magento's final totals.
            $subtotal = $this->convertToSAR($address->getSubtotal(), $quote, $roundResult);
            $taxAmount = $this->convertToSAR($address->getTaxAmount(), $quote, $roundResult);
            $shippingAmount = $this->convertToSAR($address->getShippingAmount(), $quote, $roundResult);
            $discountAmount = $this->convertToSAR(abs($address->getDiscountAmount() ?? 0.0), $quote, $roundResult);
            $grandTotal = $this->convertToSAR($quote->getGrandTotal(), $quote, $roundResult);
        }

        // Step 3: Return the final, correctly rounded values.
        return [
            'subtotal'        => $this->priceCurrency->roundPrice($subtotal),
            'tax_amount'      => $this->priceCurrency->roundPrice($taxAmount),
            'shipping_amount' => $this->priceCurrency->roundPrice($shippingAmount),
            'discount_amount' => $this->priceCurrency->roundPrice($discountAmount),
            'grand_total'     => $this->priceCurrency->roundPrice($grandTotal)
        ];
    }

    /**
     * Calculates and caches a single, stable conversion rate from a source currency to a target currency.
     *
     * @param string $fromCurrencyCode
     * @param string $toCurrencyCode
     * @param StoreInterface $store
     * @return float
     * @throws LocalizedException
     */
    private function getCombinedConversionRate(string $fromCurrencyCode, string $toCurrencyCode, StoreInterface $store): float
    {
        $cacheKey = sprintf('%s-%s', $fromCurrencyCode, $toCurrencyCode);
        if (isset($this->conversionRates[$cacheKey])) {
            return $this->conversionRates[$cacheKey];
        }

        $baseCurrencyCode = $store->getBaseCurrency()->getCode();
        /** @var Currency $baseCurrencyModel */
        $baseCurrencyModel = $this->currencyFactory->create()->load($baseCurrencyCode);

        $rateFromToBase = 1.0;
        if ($fromCurrencyCode !== $baseCurrencyCode) {
            $rate = $baseCurrencyModel->getRate($fromCurrencyCode);
            if (!$rate) {
                throw new LocalizedException(__("Unable to get exchange rate from %1 to %2", $baseCurrencyCode, $fromCurrencyCode));
            }
            $rateFromToBase = 1 / $rate;
        }

        $rateBaseToTarget = 1.0;
        if ($toCurrencyCode !== $baseCurrencyCode) {
            $rate = $baseCurrencyModel->getRate($toCurrencyCode);
            if (!$rate) {
                throw new LocalizedException(__("Unable to get exchange rate from %1 to %2", $baseCurrencyCode, $toCurrencyCode));
            }
            $rateBaseToTarget = $rate;
        }

        $this->conversionRates[$cacheKey] = $rateFromToBase * $rateBaseToTarget;

        return $this->conversionRates[$cacheKey];
    }
    /**
     * Retrieves the store object from the quote or the default store.
     *
     * @param Quote|null $quote
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    private function getStore(Quote $quote = null): StoreInterface
    {
        return $quote ? $quote->getStore() : $this->storeManager->getStore();
    }
}
