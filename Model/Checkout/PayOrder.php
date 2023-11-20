<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Checkout;

use Amwal\Payments\Model\AddressResolver;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\Data\OrderUpdate;
use Amwal\Payments\Model\ErrorReporter;
use Amwal\Payments\Model\GetAmwalOrderData;
use JsonException;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\CustomerManagement;
use Psr\Log\LoggerInterface;
use Magento\Framework\Webapi\Exception as WebapiException;

class PayOrder extends AmwalCheckoutAction
{
    private CartRepositoryInterface $quoteRepository;
    private CheckoutSession $checkoutSession;
    private InvoiceOrder $invoiceAmwalOrder;
    private GetAmwalOrderData $getAmwalOrderData;
    private OrderRepositoryInterface $orderRepository;
    private ManagerInterface $messageManager;
    private OrderPaymentRepositoryInterface $paymentRepository;
    private CustomerRepositoryInterface $customerRepository;
    private CustomerSession $customerSession;
    private CustomerManagement $customerManagement;
    private OrderUpdate $orderUpdate;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param CheckoutSession $checkoutSession
     * @param InvoiceOrder $invoiceAmwalOrder
     * @param GetAmwalOrderData $getAmwalOrderData
     * @param OrderRepositoryInterface $orderRepository
     * @param ManagerInterface $messageManager
     * @param OrderPaymentRepositoryInterface $paymentRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerSession $customerSession
     * @param CustomerManagement $customerManagement
     * @param ErrorReporter $errorReporter
     * @param Config $config
     * @param OrderUpdate $orderUpdate
     * @param LoggerInterface $logger
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        CheckoutSession $checkoutSession,
        InvoiceOrder $invoiceAmwalOrder,
        GetAmwalOrderData $getAmwalOrderData,
        OrderRepositoryInterface $orderRepository,
        ManagerInterface $messageManager,
        OrderPaymentRepositoryInterface $paymentRepository,
        CustomerRepositoryInterface $customerRepository,
        CustomerSession $customerSession,
        CustomerManagement $customerManagement,
        ErrorReporter $errorReporter,
        Config $config,
        LoggerInterface $logger,
        OrderUpdate $orderUpdate
    ) {
        parent::__construct($errorReporter, $config, $logger);
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->invoiceAmwalOrder = $invoiceAmwalOrder;
        $this->getAmwalOrderData = $getAmwalOrderData;
        $this->orderRepository = $orderRepository;
        $this->messageManager = $messageManager;
        $this->paymentRepository = $paymentRepository;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->customerManagement = $customerManagement;
        $this->orderUpdate = $orderUpdate;
    }

    /**
     * @param int $orderId
     * @param string $amwalOrderId
     * @return bool
     * @throws LocalizedException
     */
    public function execute(int $orderId, string $amwalOrderId) : bool
    {
        $order = $this->orderRepository->get($orderId);

        $amwalOrderData = $this->getAmwalOrderData->execute($amwalOrderId);
        if (!$amwalOrderData) {
            $message = sprintf('Unable to retrieve Amwal Order Data for order with ID "%s". Amwal Order id: %s', $orderId, $amwalOrderId);
            $this->logger->error($message);
            $this->reportError($amwalOrderId, $message);
            $this->addError(__('We were unable to retrieve your transaction data.'));
            return false;
        }

        try {
            $quote = $this->quoteRepository->get($order->getQuoteId());
            $quote->setData(AmwalCheckoutAction::IS_AMWAL_API_CALL, true);
        } catch (NoSuchEntityException $e) {
            $message = sprintf('Unable to load Quote for order with ID "%s". Amwal Order id: %s', $orderId, $amwalOrderId);
            $this->reportError($amwalOrderId, $message);
            $this->logger->error($message);
            $this->addError(__('We were unable to retrieve your order data.'));
            return false;
        }

        $this->updateCustomerName($order, $amwalOrderData);
        $this->updateAddressData($quote, $amwalOrderData);
        $this->updateAddressData($order, $amwalOrderData);

        if ($this->shouldCreateCustomer($order, $amwalOrderData)) {
            $this->logDebug('Creating new customer');
            try {
                $newCustomer = $this->createCustomer($order);
                $this->customerSession->setCustomerDataAsLoggedIn($newCustomer);
                $this->checkoutSession->setCustomerData($newCustomer);
            } catch (LocalizedException $e) {
                $message = sprintf(
                    'Error occurred while creating customer for order with ID %s. Exception %s',
                    $order->getEntityId(),
                    $e->getMessage()
                );
                $this->reportError($amwalOrderId, $message);
                $this->logger->error($message);
            }
        }

        $this->addAdditionalPaymentInformation($amwalOrderData, $order);
        $amwalOrderStatus = $amwalOrderData->getStatus();

        $this->checkoutSession->clearHelperData();
        $this->checkoutSession->setLastQuoteId($quote->getId())
            ->setLastSuccessQuoteId($quote->getId());

        $this->checkoutSession->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId())
            ->setLastOrderStatus($order->getStatus());

        $this->orderUpdate->update($order, $amwalOrderStatus, $historyComment);

        if($amwalOrderStatus == 'success') {
            $quote->removeAllItems();
            $this->quoteRepository->save($quote);
            return true;
        }else{
            throw new WebapiException(__('We were unable to process your transaction.'), 0, WebapiException::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param OrderInterface $order
     * @param DataObject $amwalOrderData
     * @return void
     */
    private function updateCustomerName(OrderInterface $order, DataObject $amwalOrderData): void
    {
        $order->setCustomerFirstname($amwalOrderData->getClientFirstName() ?? AddressResolver::TEMPORARY_DATA_VALUE);
        $order->setCustomerLastname($amwalOrderData->getClientLastName() ?? AddressResolver::TEMPORARY_DATA_VALUE);
        $this->orderRepository->save($order);
    }

    /**
     * @param Phrase|string|null $message
     * @return void
     */
    private function addError($message = null): void
    {
        $genericMessage = __('Something went wrong while placing your order. Please contact us to complete the order.');
        $this->messageManager->addErrorMessage($message ?? $genericMessage);
    }

    /**
     * @param CartInterface|OrderInterface $entity
     * @param DataObject $amwalOrderData
     * @return void
     */
    public function updateAddressData($entity, DataObject $amwalOrderData): void
    {
        $shippingAddress = $entity->getShippingAddress();

        $shippingAddress->setFirstname($amwalOrderData->getClientFirstName() ?? AddressResolver::TEMPORARY_DATA_VALUE);
        $shippingAddress->setLastname($amwalOrderData->getClientLastName() ?? AddressResolver::TEMPORARY_DATA_VALUE);
        $entity->setShippingAddress($shippingAddress);

        $billingAddress = $entity->getBillingAddress();
        if ($billingAddress) {
            $billingAddress->setFirstname($amwalOrderData->getClientFirstName() ?? AddressResolver::TEMPORARY_DATA_VALUE);
            $billingAddress->setLastname($amwalOrderData->getClientLastName() ?? AddressResolver::TEMPORARY_DATA_VALUE);
            $entity->setBillingAddress($billingAddress);
        }

        if ($entity instanceof CartInterface) {
            $this->quoteRepository->save($entity);
        }

        if ($entity instanceof OrderInterface) {
            $this->orderRepository->save($entity);
        }
    }

    /**
     * @param DataObject $amwalOrderData
     * @param OrderInterface $order
     * @return void
     */
    public function addAdditionalPaymentInformation(DataObject $amwalOrderData, OrderInterface $order): void
    {
        $payment = $order->getPayment();

        if (!$payment) {
            return;
        }

        $additionalInfo = $payment->getAdditionalInformation();
        $additionalInfo['payment_brand'] = $amwalOrderData->getData('paymentBrand');
        $payment->setAdditionalInformation($additionalInfo);
        $this->paymentRepository->save($payment);

        $order->setPayment($payment);
        $this->orderRepository->save($order);
    }

    /**
     * @param OrderInterface $order
     * @return CustomerInterface
     * @throws AlreadyExistsException
     */
    public function createCustomer(OrderInterface $order): CustomerInterface
    {
        $quote = $this->quoteRepository->get($order->getQuoteId());
        $quoteBillingAddress = $quote->getBillingAddress();
        if ($quoteBillingAddress) {
            $quoteBillingAddress->setSaveInAddressBook(1);
            $quote->setBillingAddress($quoteBillingAddress);
        }
        $quoteShippingAddress = $quote->getShippingAddress();
        if ($quoteShippingAddress) {
            $quoteShippingAddress->setSaveInAddressBook(1);
            $quote->setShippingAddress($quoteShippingAddress);
        }
        $this->quoteRepository->save($quote);

        $customer = $this->customerManagement->create($order->getEntityId());

        try {
            $this->logDebug(sprintf(
                'Customer created with data: %s',
                json_encode($customer->__toArray(), JSON_THROW_ON_ERROR)
            ));
        } catch (JsonException $e) {
            return $customer;
        }

        return $customer;
    }

    /**
     * @param string $email
     * @return bool
     */
    private function customerWithEmailExists(string $email): bool
    {
        try {
            $this->customerRepository->get($email);
        } catch (NoSuchEntityException|LocalizedException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param OrderInterface $order
     * @param DataObject $amwalOrderData
     * @return bool
     */
    private function shouldCreateCustomer(OrderInterface $order, DataObject $amwalOrderData): bool
    {
        if (!$email = $amwalOrderData->getClientEmail() ?? $order->getCustomerEmail()) {
            return false;
        }

        return $order->getCustomerIsGuest() &&
            !$this->customerWithEmailExists($email) &&
            $this->config->shouldCreateCustomer();
    }
}
