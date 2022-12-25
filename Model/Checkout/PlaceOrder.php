<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Checkout;

use Amwal\Payments\Api\Data\AmwalAddressInterfaceFactory;
use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Api\RefIdManagementInterface;
use Amwal\Payments\Model\AddressResolver;
use Amwal\Payments\Model\AmwalClientFactory;
use Amwal\Payments\Model\Config;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Math\Random;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\CartRepositoryInterface as QuoteRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

class PlaceOrder
{

    private AmwalClientFactory $amwalClientFactory;
    private Json $jsonSerializer;
    private QuoteManagement $quoteManagement;
    private AddressFactory $quoteAddressFactory;
    private QuoteRepositoryInterface $quoteRepository;
    private CheckoutSession $checkoutSession;
    private Config $config;
    private ManagerInterface $messageManager;
    private InvoiceOrder $invoiceAmwalOrder;
    private AddressResolver $addressResolver;
    private OrderRepositoryInterface $orderRepository;
    private Factory $objectFactory;
    private AmwalAddressInterfaceFactory $amwalAddressFactory;
    private RefIdManagementInterface $refIdManagement;
    private UpdateShippingMethod $updateShippingMethod;
    private SetAmwalOrderDetails $setAmwalOrderDetails;
    private StoreManagerInterface $storeManager;
    private CustomerRepositoryInterface $customerRepository;
    private CustomerInterfaceFactory $customerFactory;
    private AccountManagementInterface $accountManagement;
    private SessionFactory $customerSessionFactory;
    private LoggerInterface $logger;

    /**
     * @param AmwalClientFactory $amwalClientFactory
     * @param Json $jsonSerializer
     * @param QuoteManagement $quoteManagement
     * @param AddressFactory $quoteAddressFactory
     * @param QuoteRepositoryInterface $quoteRepository
     * @param CheckoutSession $checkoutSession
     * @param Config $config
     * @param ManagerInterface $messageManager
     * @param InvoiceOrder $invoiceAmwalOrder
     * @param AddressResolver $addressResolver
     * @param OrderRepositoryInterface $orderRepository
     * @param Factory $objectFactory
     * @param AmwalAddressInterfaceFactory $amwalAddressFactory
     * @param RefIdManagementInterface $refIdManagement
     * @param UpdateShippingMethod $updateShippingMethod
     * @param SetAmwalOrderDetails $setAmwalOrderDetails
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterfaceFactory $customerFactory
     * @param AccountManagementInterface $accountManagement
     * @param SessionFactory $customerSessionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        AmwalClientFactory           $amwalClientFactory,
        Json                         $jsonSerializer,
        QuoteManagement              $quoteManagement,
        AddressFactory               $quoteAddressFactory,
        QuoteRepositoryInterface     $quoteRepository,
        CheckoutSession              $checkoutSession,
        Config                       $config,
        ManagerInterface             $messageManager,
        InvoiceOrder                 $invoiceAmwalOrder,
        AddressResolver              $addressResolver,
        OrderRepositoryInterface     $orderRepository,
        Factory                      $objectFactory,
        AmwalAddressInterfaceFactory $amwalAddressFactory,
        RefIdManagementInterface     $refIdManagement,
        UpdateShippingMethod         $updateShippingMethod,
        SetAmwalOrderDetails         $setAmwalOrderDetails,
        StoreManagerInterface        $storeManager,
        CustomerRepositoryInterface  $customerRepository,
        CustomerInterfaceFactory     $customerFactory,
        AccountManagementInterface   $accountManagement,
        SessionFactory               $customerSessionFactory,
        LoggerInterface              $logger
    ) {
        $this->amwalClientFactory = $amwalClientFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->quoteManagement = $quoteManagement;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
        $this->messageManager = $messageManager;
        $this->invoiceAmwalOrder = $invoiceAmwalOrder;
        $this->addressResolver = $addressResolver;
        $this->orderRepository = $orderRepository;
        $this->objectFactory = $objectFactory;
        $this->amwalAddressFactory = $amwalAddressFactory;
        $this->refIdManagement = $refIdManagement;
        $this->updateShippingMethod = $updateShippingMethod;
        $this->setAmwalOrderDetails = $setAmwalOrderDetails;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->accountManagement = $accountManagement;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->logger = $logger;
    }

    /**
     * @param int $quoteId
     * @param string $refId
     * @param RefIdDataInterface $refIdData
     * @param string $amwalOrderId
     * @param string $triggerContext
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(int $quoteId, string $refId, RefIdDataInterface $refIdData, string $amwalOrderId, string $triggerContext): void
    {
        $amwalOrderData = $this->getAmwalOrderData($amwalOrderId);
        if (!$amwalOrderData) {
            $this->logger->error(sprintf('Unable to retrieve Amwal Order Data for quote with ID "%s". Amwal Order id: %s', $quoteId, $amwalOrderId));
            $this->throwException(__('We were unable to retrieve your transaction data.'));
        }

        if ($refId !== $amwalOrderData->getRefId() || !$this->refIdManagement->verifyRefId($refId, $refIdData)) {
            $this->logger->debug(sprintf(
                "Ref ID's don't match.\n Amwal Ref ID: %s\nInternal Ref ID: %s\nExpected Ref ID: %s\n Data used to generate ID: %s" ,
                $amwalOrderData->getRefId(),
                $refId,
                $this->refIdManagement->generateRefId($refIdData),
                $refIdData->toJson()
            ));
            $this->throwException(__('We were unable to verify your payment.'));
        }

        try {
            $customerAddress = $this->addressResolver->execute($amwalOrderData);
        } catch (LocalizedException | RuntimeException $e) {
            $this->logger->error(sprintf(
                "Unable to resolve address while creating order.\nQuote ID: %s\nAmwal Order Data: %s\nAmwal Order id: %s",
                $quoteId,
                $amwalOrderData->toJson(),
                $amwalOrderId
            ));
            $this->throwException();
        }

        $quote = $this->quoteRepository->get($quoteId);

        if ($quote->getCustomerIsGuest()) {
            $this->setCustomerEmail($quote, $amwalOrderData->getClientEmail());
        }

        $this->updateCustomerAddress($quote, $customerAddress);
        if ($amwalOrderData->getShippingDetails()) {
            $this->updateShippingMethod->execute($quote, $amwalOrderData->getShippingDetails()->getId());
        }

        $newCustomer = null;
        if ($this->shouldCreateCustomer($quote, $amwalOrderData)) {
            try {
                $newCustomer = $this->createCustomer($amwalOrderData, $customerAddress);
                $quote->setCustomerIsGuest(false);
                $quote->setCustomer($newCustomer);
                $quote->setCustomerId($newCustomer->getId());
                $quote->setAmwalUserCreated(true);
                $this->customerSessionFactory->create()->setCustomerDataAsLoggedIn($newCustomer);
            } catch (LocalizedException $e) {
                $this->logger->error(sprintf(
                    'Error while creating customer for quote with ID %s. Error: %s',
                    $quoteId,
                    $e->getMessage()
                ));
            }
        }

        $this->quoteRepository->save($quote);

        $order = $this->createOrder($quote);
        $order->setAmwalOrderId($amwalOrderId);
        $this->invoiceAmwalOrder->execute($order, $amwalOrderData);

        if ($newCustomer) {
            $order->addCommentToStatusHistory(__('Created new customer with ID %1', $newCustomer->getId()));
        }

        $this->orderRepository->save($order);

        $this->setAmwalOrderDetails->execute($order, $amwalOrderId, $triggerContext);
    }

    /**
     * @param string $amwalOrderId
     * @return DataObject|null
     */
    private function getAmwalOrderData(string $amwalOrderId): ?DataObject
    {
        $amwalClient = $this->amwalClientFactory->create();

        try {
            $response = $amwalClient->get('transactions/' . $amwalOrderId);
        } catch (GuzzleException $e) {
            $this->logger->error(sprintf(
                'Unable to retrieve Order data from Amwal. Exception: %s',
                $e->getMessage()
            ));
            return null;
        }

        $responseData = $response->getBody()->getContents();
        $responseData = $this->jsonSerializer->unserialize($responseData);

        $amwalOrderData = $this->objectFactory->create($responseData);

        if ($amwalOrderData->getAddressDetails()) {
            $amwalOrderAddress = $this->amwalAddressFactory->create()->setData($amwalOrderData->getAddressDetails());
            $amwalOrderData->setAddressDetails($amwalOrderAddress);
        }

        if ($amwalOrderData->getShippingDetails()) {
            $shippingDetails = $this->objectFactory->create($amwalOrderData->getShippingDetails());
            $amwalOrderData->setShippingDetails($shippingDetails);
        }

        return $amwalOrderData;
    }

    /**
     * @param Quote $quote
     * @return OrderInterface
     * @throws LocalizedException
     */
    private function createOrder(Quote $quote): OrderInterface
    {
        $order = $this->quoteManagement->submit($quote);

        if (!$order) {
            $this->logger->error(sprintf('Unable create an order because we failed to submit the quote with ID "%s"', $quote->getId()));
            $this->throwException();
        }

        $order->setEmailSent(0);
        if (!$order->getEntityId()) {
            $this->logger->error(sprintf('Order could not be created from quote with ID "%s"', $quote->getId()));
            $this->throwException();
        }

        $order->setState($this->config->getOrderConfirmedStatus());
        $order->setStatus($this->config->getOrderConfirmedStatus());

        $this->checkoutSession->clearHelperData();
        $this->checkoutSession->setLastQuoteId($quote->getId())
            ->setLastSuccessQuoteId($quote->getId());

        $this->checkoutSession->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId())
            ->setLastOrderStatus($order->getStatus());

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
     * @return void
     * @throws LocalizedException
     */
    private function throwException($message = null): void
    {
        $this->messageManager->addErrorMessage($this->getGenericErrorMessage());
        throw new LocalizedException($message ?? $this->getGenericErrorMessage());
    }

    /**
     * Update the customer address, since we need to replace temporary data.
     * @param CartInterface $quote
     * @param AddressInterface $customerAddress
     * @return void
     */
    private function updateCustomerAddress(CartInterface $quote, AddressInterface $customerAddress): void
    {
        $quoteAddress = $this->quoteAddressFactory->create();
        $quoteAddress->importCustomerAddressData($customerAddress);
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
     * @param DataObject $amwalOrderData
     * @param AddressInterface $customerAddress
     * @return CustomerInterface
     * @throws LocalizedException
     */
    private function createCustomer(DataObject $amwalOrderData, AddressInterface $customerAddress): CustomerInterface
    {
        $customer = $this->customerFactory->create();
        $customer->setEmail($amwalOrderData->getClientEmail());
        $customer->setFirstname($amwalOrderData->getClientFirstName());
        $customer->setLastname($amwalOrderData->getClientLastName());
        $customer->setAddresses([$customerAddress]);

        return $this->accountManagement->createAccount($customer);
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
     * @param CartInterface $quote
     * @param DataObject $amwalOrderData
     * @return bool
     */
    private function shouldCreateCustomer(CartInterface $quote, DataObject $amwalOrderData): bool
    {
        return $quote->getCustomerIsGuest() &&
            !$this->customerWithEmailExists($amwalOrderData->getClientEmail()) &&
            $this->config->shouldCreateCustomer();
    }
}
