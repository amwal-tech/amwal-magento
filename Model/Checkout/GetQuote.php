<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Checkout;

use Amwal\Payments\Plugin\Sentry\SentryExceptionReport;
use Amwal\Payments\Api\Data\AmwalAddressInterface;
use Amwal\Payments\Api\Data\AmwalAddressInterfaceFactory;
use Amwal\Payments\Api\Data\AmwalOrderItemInterface;
use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Api\RefIdManagementInterface;
use Amwal\Payments\Model\AddressResolver;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\Config\Checkout\ConfigProvider;
use Amwal\Payments\Model\ErrorReporter;
use JsonException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Group as CustomerGroup;
use Magento\Customer\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Quote\Api\CartRepositoryInterface as QuoteRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ShippingMethodManagement;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;
use Magento\Quote\Model\QuoteIdMaskFactory;

class GetQuote extends AmwalCheckoutAction
{
    private CustomerRepositoryInterface $customerRepository;
    private Session $customerSession;
    private QuoteFactory $quoteFactory;
    private StoreManagerInterface $storeManager;
    private ProductRepositoryInterface $productRepository;
    private AmwalAddressInterfaceFactory $amwalAddressFactory;
    private AddressFactory $quoteAddressFactory;
    private QuoteRepositoryInterface $quoteRepository;
    private ManagerInterface $messageManager;
    private ShippingMethodManagement $shippingMethodManagement;
    private AddressResolver $addressResolver;
    private Factory $objectFactory;
    private RefIdManagementInterface $refIdManagement;
    private MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId;
    private CheckoutSession $checkoutSession;
    private SentryExceptionReport $sentryExceptionReport;
    private QuoteIdMaskFactory $quoteIdMaskFactory;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param Session $customerSession
     * @param QuoteFactory $quoteFactory
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param AmwalAddressInterfaceFactory $amwalAddressFactory
     * @param AddressFactory $quoteAddressFactory
     * @param QuoteRepositoryInterface $quoteRepository
     * @param ManagerInterface $messageManager
     * @param ShippingMethodManagement $shippingMethodManagement
     * @param AddressResolver $addressResolver
     * @param Factory $objectFactory
     * @param RefIdManagementInterface $refIdManagement
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CheckoutSession $checkoutSession
     * @param SentryExceptionReport $sentryExceptionReport
     * @param ErrorReporter $errorReporter
     * @param Config $config
     * @param LoggerInterface $logger
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession,
        QuoteFactory $quoteFactory,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        AmwalAddressInterfaceFactory $amwalAddressFactory,
        AddressFactory $quoteAddressFactory,
        QuoteRepositoryInterface $quoteRepository,
        ManagerInterface $messageManager,
        ShippingMethodManagement $shippingMethodManagement,
        AddressResolver $addressResolver,
        Factory $objectFactory,
        RefIdManagementInterface $refIdManagement,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        CheckoutSession $checkoutSession,
        SentryExceptionReport $sentryExceptionReport,
        ErrorReporter $errorReporter,
        Config $config,
        LoggerInterface $logger,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        parent::__construct($errorReporter, $config, $logger);
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->amwalAddressFactory = $amwalAddressFactory;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->quoteRepository = $quoteRepository;
        $this->messageManager = $messageManager;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->addressResolver = $addressResolver;
        $this->objectFactory = $objectFactory;
        $this->refIdManagement = $refIdManagement;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->checkoutSession = $checkoutSession;
        $this->sentryExceptionReport = $sentryExceptionReport;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * @param AmwalOrderItemInterface[] $orderItems
     * @param string $refId
     * @param RefIdDataInterface $refIdData
     * @param string[] $addressData
     * @param string $triggerContext
     * @param bool $isPreCheckout
     * @param string|null $cartId
     * @return mixed[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(
        array $orderItems,
        string $refId,
        RefIdDataInterface $refIdData,
        array $addressData,
        string $triggerContext,
        bool $isPreCheckout,
        $cartId = null
    ): array {
        $quoteData = [];
        $customerAddress = null;

        try {
            $this->logDebug('Start GetQuote call');
            if (!$this->refIdManagement->verifyRefId($refId, $refIdData)) {
                $this->logger->error(sprintf(
                    "Unable to get quote because Ref ID cannot be verified.\nReceived Ref ID: %s\nExpected Ref ID: %s\nRef ID Data: %s",
                    $refId,
                    $this->refIdManagement->generateRefId($refIdData),
                    $refIdData->toJson()
                ));
                $this->throwException(__('We are unable to verify the reference ID of this payment'));
            }

            if (!$cartId) {
                $cartId = $this->quoteIdMaskFactory->create()->load($this->checkoutSession->getQuote()->getId(), 'quote_id')->getMaskedId();
            }
            $quoteId = $this->maskedQuoteIdToQuoteId->execute($cartId);
            $quote = $this->getQuote($quoteId, $orderItems, $triggerContext);
            if (!$quote->getItems()) {
                $this->throwException(__('One or more selected products are currently not available.'));
            }

            // Fix for Magento 2.4.0 where the quote is marked as not being a guest quote, even though it is.
            if (!$quote->getCustomerId() && !$quote->getCustomerIsGuest()) {
                $quote->setCustomerIsGuest(true);
            }

            $amwalAddress = $this->amwalAddressFactory->create(['data' => $addressData]);
            if (!$isPreCheckout) {
                $amwalOrderData = $this->objectFactory->create([
                    'client_first_name' => $addressData['client_first_name'] ?? AddressResolver::TEMPORARY_DATA_VALUE,
                    'client_last_name' => $addressData['client_last_name'] ?? AddressResolver::TEMPORARY_DATA_VALUE,
                    'client_phone_number' => $addressData['client_phone_number'] ?? AddressResolver::TEMPORARY_DATA_VALUE,
                    'client_email' => $addressData['client_email'] ?? AddressResolver::TEMPORARY_DATA_VALUE,
                ]);
                $amwalOrderData->setAddressDetails($amwalAddress);
                $customerAddress = $this->getCustomerAddress($amwalOrderData, $refId, $quote->getCustomerId());
            }

            $quote->setData(self::IS_AMWAL_API_CALL, true);
            $quote->getPayment()->setQuote($quote);
            $quote->setPaymentMethod(ConfigProvider::CODE);
            $quote->getPayment()->importData(['method' => ConfigProvider::CODE]);
            
            $availableRates = [];
            if (!$isPreCheckout) {
                $quoteAddress = $this->getQuoteAddress($customerAddress, $amwalAddress);

                $this->logDebug('Setting Billing and Shipping address');
                $quote->setBillingAddress($quoteAddress);
                $quote->setShippingAddress($quoteAddress);

                $this->logDebug('Collecting shipping rates');
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->getShippingAddress()->collectShippingRates();
                $this->quoteRepository->save($quote);

                $availableRates = $this->getAvailableRates($quote);
            }

            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();
            $this->quoteRepository->save($quote);

            $responseData = $this->getResponseData($quote, $availableRates);

            $quoteData = [
                'data' => $responseData
            ];

            try {
                $this->logDebug(sprintf('End GetQuote call. Data: %s', json_encode($quoteData, JSON_THROW_ON_ERROR)));
            } catch (JsonException $e) {
                $this->logger->notice('Unable to log quote data debug message');
            }
        } catch (Throwable $e) {
            $this->reportError($refId, $e->getMessage());
            $this->throwException($e->getMessage(), $e);
        }

        return $quoteData;
    }

    /**
     * @return CustomerInterface|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getSessionCustomer(): ?CustomerInterface
    {
        $customerId = $this->getSessionCustomerId();
        return $customerId ? $this->customerRepository->getById($customerId) : null;
    }

    /**
     * @return int|null
     */
    private function getSessionCustomerId(): ?int
    {
        return (int) $this->customerSession->getCustomerId() ?: null;
    }

    /**
     * @return Phrase
     */
    private function getGenericErrorMessage(): Phrase
    {
        return __('Something went wrong while placing your order. Please contact us to complete the order.');
    }

    /**
     * @param Phrase|string|null $message
     * @param Throwable|null $originalException
     * @return void
     * @throws LocalizedException
     */
    private function throwException($message = null, Throwable $originalException = null): void
    {
        if($originalException){
            $this->sentryExceptionReport->report($originalException);
        }
        $this->messageManager->addErrorMessage($this->getGenericErrorMessage());
        $message = $message ?? $this->getGenericErrorMessage();
        throw new LocalizedException(
            is_string($message) ? __($message) : $message
        );
    }

    /**
     * @param array $orderItems
     * @return Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function createQuote(array $orderItems): Quote
    {
        $quote = $this->quoteFactory->create();
        $quote->setStore($this->storeManager->getStore());
        $quote->setCurrency();

        if ($customer = $this->getSessionCustomer()) {
            $quote->assignCustomer($customer);
        } else if (!$quote->getCustomerId()) {
            $quote->setCustomerIsGuest(true)
                ->setCustomerGroupId(CustomerGroup::NOT_LOGGED_IN_ID);
        }

        foreach ($orderItems as $item) {
            $product = $this->productRepository->getById($item->getProductId());

            $request = $this->objectFactory->create();
            $request->setData('qty', $item->getQty());

            if ($item->getConfiguredProductId() && $item->getSelectedConfigurableOptions()) {
                $request->setData('product', $item->getConfiguredProductId());
                $request->setData('super_attribute', $item->getSelectedConfigurableOptions());
            }

            $quote->addProduct(
                $product,
                $request
            );
        }

        $this->quoteRepository->save($quote);

        return $quote;
    }

    /**
     * @param DataObject $amwalOrderData
     * @param string $refId
     * @param int|string|null $customerId
     * @return AddressInterface
     * @throws JsonException
     * @throws LocalizedException
     */
    public function getCustomerAddress(DataObject $amwalOrderData, string $refId, mixed $customerId): AddressInterface
    {
        $customerAddress = null;
        try {
            $this->logDebug(sprintf(
                'Resolving customer address using Amwal order data: %s',
                $amwalOrderData->toJson()
            ));
            $customerAddress = $this->addressResolver->execute($amwalOrderData, $customerId);
            $this->logDebug(sprintf(
                'Resolved customer address with data: %s',
                json_encode($customerAddress->__toArray(), JSON_THROW_ON_ERROR)
            ));
        } catch (LocalizedException|RuntimeException $e) {
            $message = sprintf(
                'Unable to resolve customer address with Data %s. Received exception %s',
                $amwalOrderData->toJson(),
                $e->getMessage()
            );
            $this->reportError($refId, $message);
            $this->logger->error($message);
            $this->throwException(__('Something went wrong while processing you address information.'), $e);
        }
        return $customerAddress;
    }

    /**
     * @param $quoteId
     * @param array $orderItems
     * @param string $triggerContext
     * @return CartInterface|Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuote($quoteId, array $orderItems, string $triggerContext)
    {
        if (!$quoteId) {
            $quote = $this->checkoutSession->getQuote();
            if ($quote) {
                $this->logDebug(sprintf('Quote with ID %s found.', $quote->getId()));
                return $quote;
            }
            $this->logDebug('No quote found. Creating a new quote');
            $quote = $this->createQuote($orderItems);
            $this->logDebug('Quote created');
        } else {
            if (!is_numeric($quoteId)) {
                $quoteId = $this->maskedQuoteIdToQuoteId->execute($quoteId);
            }
            $this->logDebug(sprintf('Quote ID %s provided. Loading quote', $quoteId));
            $quote = $this->quoteRepository->get($quoteId);
        }
        if ($this->config->isQuoteOverrideEnabled()) {
            $quote->setStoreId($this->storeManager->getStore()->getId());
        }
        return $quote;
    }

    /**
     * @param AddressInterface $customerAddress
     * @param AmwalAddressInterface $amwalAddress
     * @return Address
     */
    public function getQuoteAddress(AddressInterface $customerAddress, AmwalAddressInterface $amwalAddress): Address
    {
        $this->logDebug('Creating quote address');
        $quoteAddress = $this->quoteAddressFactory->create();
        $quoteAddress->importCustomerAddressData($customerAddress);

        if ($customerEmail = $amwalAddress->getEmail()) {
            $this->logDebug(sprintf('Setting customer email for quote address to %s', $customerEmail));
            $quoteAddress->setEmail($customerEmail);
        }
        return $quoteAddress;
    }

    /**
     * @param $quote
     * @return mixed[]
     * @throws LocalizedException
     */
    public function getAvailableRates($quote): array
    {
        $rates = $this->shippingMethodManagement->estimateByExtendedAddress($quote->getId(), $quote->getShippingAddress());

        if (!$rates) {
            $this->logger->error('No shipping methods were found for the quote.');
            return [];
        }

        $availableRates = [];

        foreach ($rates as $rate) {
            $id = $rate->getCarrierCode() . '_' . $rate->getMethodCode();
            if (empty($rate->getMethodTitle())) {
                $this->logger->warning('Shipping method title is empty. Falling back to ID as title: ' . $id);
            }
            if ($rate->getAvailable()) {
                $availableRates[$id] = [
                    'carrier_title' => $rate->getMethodTitle() ?? $id,
                    'price' => number_format((float)$rate->getPriceInclTax(), 2)
                ];
            }else{
                $this->logger->warning(sprintf(
                    'Shipping method: %s has an error: %s',
                    $id,
                    $rate->getErrorMessage()
                ));
            }
        }
        try {
            $this->logDebug(sprintf(
                'Collected rates: %s',
                json_encode($rates, JSON_THROW_ON_ERROR)
            ));
        } catch (JsonException $e) {
            $this->logger->notice('Unable to log rates debug message');
        }

        return $availableRates;
    }

    /**
     * @param CartInterface $quote
     * @param array $availableRates
     * @return mixed[]
     */
    public function getResponseData(CartInterface $quote, array $availableRates): array
    {
        $useBaseCurrency = $this->config->shouldUseBaseCurrency();
        $shippingAddress = $quote->getShippingAddress();
        $taxAmount = $useBaseCurrency ? $shippingAddress->getBaseTaxAmount() : $shippingAddress->getTaxAmount();
        $cartId = $this->quoteIdMaskFactory->create()->load($quote->getId(), 'quote_id')->getMaskedId();
        return [
            'cart_id' => $cartId,
            'available_rates' => $availableRates,
            'amount' => $this->getAmount($quote, $useBaseCurrency),
            'subtotal' => $this->getSubtotal($shippingAddress, $taxAmount, $useBaseCurrency),
            'tax_amount' => $taxAmount,
            'shipping_amount' => $useBaseCurrency ? $shippingAddress->getBaseShippingInclTax() : $shippingAddress->getShippingInclTax(),
            'discount_amount' => $useBaseCurrency ? abs($shippingAddress->getBaseDiscountAmount()) : abs($shippingAddress->getDiscountAmount()),
            'additional_fee_amount' => $this->getAdditionalFeeAmount($quote),
            'additional_fee_description' => $this->getAdditionalFeeDescription($quote)
        ];
    }

    /**
     * This method can be extended to pass any additional fees that should be displayed in the Amwal amount summary
     * Currently we have built-in support for the Amasty Extrafee extension
     * @param CartInterface $quote
     * @return float
     * @see getAdditionalFeeDescription()
     */
    public function getAdditionalFeeAmount(CartInterface $quote): float
    {
        $extraFee = 0;
        $totals = $quote->getTotals();
        if (isset($totals['amasty_extrafee'])) {
            $extraFee = $totals['amasty_extrafee']->getValueInclTax();
        }

        return $extraFee;
    }

    /**
     * This method can be overwritten to provide a description for any additional fees that should be displayed in the Amwal amount summary
     * Currently we have built-in support for the Amasty Extrafee extension
     * @param CartInterface $quote
     * @return Phrase|string
     * @see getAdditionalFeeAmount()
     */
    public function getAdditionalFeeDescription(CartInterface $quote)
    {
        $feeDescription = '';
        $totals = $quote->getTotals();
        if ($quote->getData('applied_amasty_fee_flag') && isset($totals['amasty_extrafee'])) {
            $feeArguments = $totals['amasty_extrafee']->getTitle()->getArguments();
            $feeDescription = reset($feeArguments);
        }

        return $feeDescription;
    }

    /**
     * @param CartInterface $quote
     * @param bool $useBaseCurrency
     * @return float
     * @throws LocalizedException
     */
    public function getAmount(CartInterface $quote, bool $useBaseCurrency): float
    {
        $grandTotal = $useBaseCurrency ? $quote->getBaseGrandTotal() : $quote->getGrandTotal();

        $totals = $quote->getTotals();
        if ($quote->getData('applied_amasty_fee_flag') && isset($totals['amasty_extrafee'])) {
            $extraFee = $totals['amasty_extrafee']->getValueInclTax();
            $grandTotal -= $extraFee;
        }
        if (!$grandTotal) {
            $this->throwException(__('Unable to calculate order total'));
        }
        return $grandTotal;
    }

    /**
     * @param Address $shippingAddress
     * @param float $taxAmount
     * @param bool $useBaseCurrency
     * @return float
     */
    public function getSubtotal(Address $shippingAddress, float $taxAmount, bool $useBaseCurrency): float
    {
        return ($useBaseCurrency ? $shippingAddress->getBaseSubtotalTotalInclTax() : $shippingAddress->getSubtotalInclTax()) - $taxAmount;
    }
}
