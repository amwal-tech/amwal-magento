<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Checkout;

use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Api\RefIdManagementInterface;
use Amwal\Payments\Model\AddressResolver;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\Config\Checkout\ConfigProvider;
use Amwal\Payments\Model\ErrorReporter;
use Amwal\Payments\Model\GetAmwalOrderData;
use Amwal\Payments\Plugin\Sentry\SentryExceptionReport;
use JsonException;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Quote\Api\CartRepositoryInterface as QuoteRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Magento\Framework\Api\SearchCriteriaBuilder;

class PlaceOrder extends AmwalCheckoutAction
{
    private QuoteManagement $quoteManagement;
    private AddressFactory $quoteAddressFactory;
    private QuoteRepositoryInterface $quoteRepository;
    private ManagerInterface $messageManager;
    private AddressResolver $addressResolver;
    private OrderRepositoryInterface $orderRepository;
    private RefIdManagementInterface $refIdManagement;
    private UpdateShippingMethod $updateShippingMethod;
    private SetAmwalOrderDetails $setAmwalOrderDetails;
    private MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId;
    private GetAmwalOrderData $getAmwalOrderData;
    private SentryExceptionReport $sentryExceptionReport;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @param QuoteManagement $quoteManagement
     * @param AddressFactory $quoteAddressFactory
     * @param QuoteRepositoryInterface $quoteRepository
     * @param ManagerInterface $messageManager
     * @param AddressResolver $addressResolver
     * @param OrderRepositoryInterface $orderRepository
     * @param RefIdManagementInterface $refIdManagement
     * @param UpdateShippingMethod $updateShippingMethod
     * @param SetAmwalOrderDetails $setAmwalOrderDetails
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param GetAmwalOrderData $getAmwalOrderData
     * @param ErrorReporter $errorReporter
     * @param SentryExceptionReport $sentryExceptionReport
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        QuoteManagement $quoteManagement,
        AddressFactory $quoteAddressFactory,
        QuoteRepositoryInterface $quoteRepository,
        ManagerInterface $messageManager,
        AddressResolver $addressResolver,
        OrderRepositoryInterface $orderRepository,
        RefIdManagementInterface $refIdManagement,
        UpdateShippingMethod $updateShippingMethod,
        SetAmwalOrderDetails $setAmwalOrderDetails,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        GetAmwalOrderData $getAmwalOrderData,
        ErrorReporter $errorReporter,
        SentryExceptionReport $sentryExceptionReport,
        Config $config,
        LoggerInterface $logger,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($errorReporter, $config, $logger);
        $this->quoteManagement = $quoteManagement;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->quoteRepository = $quoteRepository;
        $this->messageManager = $messageManager;
        $this->addressResolver = $addressResolver;
        $this->orderRepository = $orderRepository;
        $this->refIdManagement = $refIdManagement;
        $this->updateShippingMethod = $updateShippingMethod;
        $this->setAmwalOrderDetails = $setAmwalOrderDetails;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->getAmwalOrderData = $getAmwalOrderData;
        $this->sentryExceptionReport = $sentryExceptionReport;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param string|int $cartId
     * @param string $refId
     * @param RefIdDataInterface $refIdData
     * @param string $amwalOrderId
     * @param string $triggerContext
     * @param bool $hasAmwalAddress
     * @return OrderInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(
        $cartId,
        string $refId,
        RefIdDataInterface $refIdData,
        string $amwalOrderId,
        string $triggerContext,
        bool $hasAmwalAddress
    ): OrderInterface {
        $amwalOrderData = $this->getAmwalOrderData->execute($amwalOrderId);
        if (!$amwalOrderData) {
            $this->logger->error(sprintf('Unable to retrieve Amwal Order Data for cart with ID "%s". Amwal Order id: %s', $cartId, $amwalOrderId));
            $this->throwException(__('We were unable to retrieve your transaction data.'));
        }

        $this->logDebug(sprintf(
            'Received Amwal Order data for order with ID %s: %s',
            $amwalOrderId,
            $amwalOrderData->toJson()
        ));

        if ($refId !== $amwalOrderData->getRefId() || !$this->refIdManagement->verifyRefId($refId, $refIdData)) {
            $message = sprintf(
                "Ref ID's don't match.\n Amwal Ref ID: %s\nInternal Ref ID: %s\nExpected Ref ID: %s\n Data used to generate ID: %s" ,
                $amwalOrderData->getRefId(),
                $refId,
                $this->refIdManagement->generateRefId($refIdData),
                $refIdData->toJson()
            );
            $this->logDebug($message);
            $this->reportError($amwalOrderId, $message);
            $this->throwException(__('We were unable to verify your payment.'));
        }

        if (!is_numeric($cartId)) {
            $quoteId = $this->maskedQuoteIdToQuoteId->execute($cartId);
        }

        $quoteId = (int) $quoteId;
        $quote = $this->quoteRepository->get($quoteId);

        $quote->setData(self::IS_AMWAL_API_CALL, true);
        $quote->setPaymentMethod(ConfigProvider::CODE);
        $quote->getPayment()->importData(['method' => ConfigProvider::CODE]);

        $customerAddress = null;
        if ($hasAmwalAddress) {
            try {
                $this->logDebug('Resolving customer address');
                $customerAddress = $this->addressResolver->execute($amwalOrderData);
                try {
                    $this->logDebug(sprintf(
                        'Found/Created customer address with data: %s',
                        json_encode([
                            'street' => implode(' ', $customerAddress->getStreet() ?? []),
                            'city' => $customerAddress->getCity(),
                            'postcode' => $customerAddress->getPostcode(),
                            'region_id' => $customerAddress->getRegion() ? $customerAddress->getRegion()->getRegionCode() : null,
                            'country_id' => $customerAddress->getCountryId(),
                            'firstname' => $customerAddress->getFirstname(),
                            'lastname' => $customerAddress->getLastname(),
                            'telephone' => $customerAddress->getTelephone(),
                        ], JSON_THROW_ON_ERROR)
                    ));
                } catch (JsonException $e) {
                    $this->logger->notice('Unable to log customer data.');
                }
            } catch (LocalizedException|RuntimeException $e) {
                $message = sprintf(
                    "Unable to resolve address while creating order.\nQuote ID: %s\nAmwal Order Data: %s\nAmwal Order id: %s",
                    $quoteId,
                    $amwalOrderData->toJson(),
                    $amwalOrderId
                );
                $this->reportError($amwalOrderId, $message);
                $this->logger->error($message);
                $this->throwException($message, $e);
            }

            $amwalClientEmail = $amwalOrderData->getClientEmail();
            if ($amwalClientEmail && $quote->getCustomerEmail() !== $amwalClientEmail) {
                $this->setCustomerEmail($quote, $amwalClientEmail);
            }

            $this->updateCustomerAddress($quote, $customerAddress);
            if ($amwalOrderData->getShippingDetails()) {
                $this->updateShippingMethod->execute($quote, $amwalOrderData->getShippingDetails()->getId());
            }
        }

        if (!$quote->getShippingAddress()->getEmail() && $quote->getBillingAddress() && $quote->getBillingAddress()->getEmail()) {
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setEmail($quote->getBillingAddress()->getEmail());
            $quote->setShippingAddress($shippingAddress);
            $this->quoteRepository->save($quote);
        }

        if (!$quote->getCustomerEmail()) {
            $customerEmail = $quote->getShippingAddress()->getEmail();
            $quote->setCustomerEmail($customerEmail);
            $this->quoteRepository->save($quote);
        }

        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $this->quoteRepository->save($quote);

        $order = $this->createOrder($quote, $amwalOrderId, $refId);

        $this->orderRepository->save($order);

        $this->setAmwalOrderDetails->execute($order, $amwalOrderId, $triggerContext);

        $quote->setIsActive(true)->save();

        return $order;
    }

    /**
     * @param Quote $quote
     * @param string $amwalOrderId
     * @param string $refId
     * @return OrderInterface
     * @throws LocalizedException
     */
    public function createOrder(Quote $quote, string $amwalOrderId, string $refId): OrderInterface
    {
        $this->logDebug(sprintf('Submitting quote with ID %s', $quote->getId()));
        $order = $this->getOrderByAmwalOrderId($amwalOrderId);

        if ($order && $order->getState() !== Order::STATE_PROCESSING) {
            $this->logDebug(
                sprintf('Existing order with ID %s found. Canceling order and re-submitting quote.', $order->getEntityId())
            );
            $order->cancel();
            $order->setAmwalOrderId($amwalOrderId . '-canceled');
            $this->orderRepository->save($order);
        }

        $order = $this->quoteManagement->submit($quote);

        $this->logDebug(sprintf('Quote with ID %s has been submitted', $quote->getId()));

        if (!$order) {
            $message = sprintf('Unable create an order because we failed to submit the quote with ID "%s"', $quote->getId());
            $this->reportError($amwalOrderId, $message);
            $this->logger->error($message);
            $this->throwException();
        }

        if (!$order->getEntityId()) {
            $message = sprintf('Order could not be created from quote with ID "%s"', $quote->getId());
            $this->reportError($amwalOrderId, $message);
            $this->logger->error($message);
            $this->throwException();
        }

        $this->logDebug(sprintf('Updating order state and status for order with ID %s', $order->getEntityId()));

        $order->setState(Order::STATE_PENDING_PAYMENT);
        $order->setStatus(Order::STATE_PENDING_PAYMENT);
        $order->setAmwalOrderId($amwalOrderId);
        $order->addCommentToStatusHistory('Amwal Transaction ID: ' . $amwalOrderId);
        $order->setRefId($refId);

        return $order;
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
        if ($originalException) {
            $this->sentryExceptionReport->report($originalException);
        }
        $this->messageManager->addErrorMessage($this->getGenericErrorMessage());
        throw new LocalizedException($message ?? $this->getGenericErrorMessage());
    }

    /**
     * Update the customer address, since we need to replace temporary data.
     * @param CartInterface $quote
     * @param AddressInterface $customerAddress
     * @return void
     */
    public function updateCustomerAddress(CartInterface $quote, AddressInterface $customerAddress): void
    {
        $quoteAddress = $this->quoteAddressFactory->create();
        $quoteAddress->importCustomerAddressData($customerAddress);
        $quoteAddress->setEmail($quote->getCustomerEmail());
        $quote->setBillingAddress($quoteAddress);
        $quote->setShippingAddress($quoteAddress);
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->getShippingAddress()->collectShippingRates();
        $this->quoteRepository->save($quote);
    }

    /**
     * @param CartInterface $quote
     * @param string $customerEmail
     * @return void
     */
    private function setCustomerEmail(CartInterface $quote, string $customerEmail): void
    {
        $quote->setCustomerEmail($customerEmail);

        $billingAddress = $quote->getBillingAddress();
        if ($billingAddress && !$billingAddress->getEmail()) {
            $billingAddress->setEmail($customerEmail);
            $quote->setBillingAddress($billingAddress);
        }

        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress && !$shippingAddress->getEmail()) {
            $shippingAddress->setEmail($customerEmail);
            $quote->setShippingAddress($shippingAddress);
        }

        $this->quoteRepository->save($quote);
    }

    /**
     * @param $amwalOrderId
     * @return OrderInterface|null
     */
    private function getOrderByAmwalOrderId($amwalOrderId): ?OrderInterface
    {
        // Build a search criteria to filter orders by custom attribute
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('amwal_order_id', $amwalOrderId);
        $searchCriteria = $searchCriteria->create();

        // Search for order with the provided custom attribute value and get the order data
        $orders = $this->orderRepository->getList($searchCriteria)->getItems();
        return $orders ? reset($orders) : null;
    }
}
