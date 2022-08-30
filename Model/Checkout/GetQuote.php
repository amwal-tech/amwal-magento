<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Checkout;

use Amwal\Payments\Api\Data\AmwalAddressInterface;
use Amwal\Payments\Api\Data\AmwalOrderItemInterface;
use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Api\RefIdManagementInterface;
use Amwal\Payments\Model\AddressResolver;
use Amwal\Payments\Model\Config\ConfigProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Quote\Api\CartRepositoryInterface as QuoteRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ShippingMethodManagement;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

class GetQuote
{
    private CustomerRepositoryInterface $customerRepository;
    private Session $customerSession;
    private QuoteFactory $quoteFactory;
    private StoreManagerInterface $storeManager;
    private ProductRepositoryInterface $productRepository;
    private AddressFactory $quoteAddressFactory;
    private QuoteRepositoryInterface $quoteRepository;
    private ManagerInterface $messageManager;
    private ShippingMethodManagement $shippingMethodManagement;
    private AddressResolver $addressResolver;
    private Factory $objectFactory;
    private RefIdManagementInterface $refIdManagement;
    private LoggerInterface $logger;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param Session $customerSession
     * @param QuoteFactory $quoteFactory
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param AddressFactory $quoteAddressFactory
     * @param QuoteRepositoryInterface $quoteRepository
     * @param ManagerInterface $messageManager
     * @param ShippingMethodManagement $shippingMethodManagement
     * @param AddressResolver $addressResolver
     * @param Factory $objectFactory
     * @param RefIdManagementInterface $refIdManagement
     * @param LoggerInterface $logger
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession,
        QuoteFactory               $quoteFactory,
        StoreManagerInterface      $storeManager,
        ProductRepositoryInterface $productRepository,
        AddressFactory             $quoteAddressFactory,
        QuoteRepositoryInterface   $quoteRepository,
        ManagerInterface           $messageManager,
        ShippingMethodManagement   $shippingMethodManagement,
        AddressResolver            $addressResolver,
        Factory                    $objectFactory,
        RefIdManagementInterface   $refIdManagement,
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->quoteRepository = $quoteRepository;
        $this->messageManager = $messageManager;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->addressResolver = $addressResolver;
        $this->objectFactory = $objectFactory;
        $this->refIdManagement = $refIdManagement;
        $this->logger = $logger;
    }

    /**
     * @param AmwalOrderItemInterface $orderItems
     * @param string $refId
     * @param RefIdDataInterface $refIdData
     * @param AmwalAddressInterface $addressData
     * @param int|null $quoteId
     * @return mixed[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function execute(array $orderItems, string $refId, RefIdDataInterface $refIdData, AmwalAddressInterface $addressData, ?int $quoteId = null): array
    {
        if (!$this->refIdManagement->verifyRefId($refId, $refIdData)) {
            $this->logger->error(sprintf(
                "Unable to get quote because Ref ID cannot be verified.\nReceived Ref ID: %s\nExpected Ref ID: %s\nRef ID Data: %s",
                $refId,
                $this->refIdManagement->generateRefId($refIdData),
                $refIdData->toJson()
            ));
            $this->throwException(__('We are unable to verify the reference ID of this payment'));
        }

        $amwalOrderData = $this->objectFactory->create([
            'client_first_name' => 'tmp',
            'client_last_name' => 'tmp',
            'client_phone_number' => 'tmp'
        ]);
        $amwalOrderData->setAddressDetails($addressData);

        try {
            $customerAddress = $this->addressResolver->execute($amwalOrderData);
        } catch (LocalizedException | RuntimeException $e) {
            $this->logger->error('Unable to resolve customer address while getting the Quote');
            $this->throwException(__($e->getMessage()));
        }

        if (!$quoteId) {
            $quote = $this->createQuote($orderItems);
        } else {
            $quote = $this->quoteRepository->get($quoteId);
        }

        $quoteAddress = $this->quoteAddressFactory->create();
        $quoteAddress->importCustomerAddressData($customerAddress);

        $quote->setBillingAddress($quoteAddress);
        $quote->setShippingAddress($quoteAddress);

        $quote->setPaymentMethod(ConfigProvider::CODE);

        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->getShippingAddress()->collectShippingRates();
        $this->quoteRepository->save($quote);

        $rates = $this->shippingMethodManagement->getList($quote->getId());

        if (!$rates) {
            $this->logger->error('No shipping methods were found for the quote.');
            $this->throwException(__('There are no shipping methods available for this order.'));
        }

        $availableRates = [];

        foreach ($rates as $rate) {
            $id = $rate->getCarrierCode() . '_' . $rate->getMethodCode();
            $availableRates[$id] = [
                'carrier_title' => $rate->getMethodTitle(),
                'price' => $rate->getBaseAmount()
            ];
        }

        $quote->getPayment()->importData(['method' => ConfigProvider::CODE]);
        $quote->collectTotals();
        $this->quoteRepository->save($quote);

        return [
            'data' => [
                'quote_id' => $quote->getId(),
                'available_rates' => $availableRates,
                'amount' => $quote->getBaseGrandTotal()
            ]
        ];
    }

    /**
     * @return CustomerInterface|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getCustomer(): ?CustomerInterface
    {
        $customerId = $this->getCustomerId();
        return $customerId ? $this->customerRepository->getById($customerId) : null;
    }

    /**
     * @return int|null/
     */
    private function getCustomerId(): ?int
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
     * @return void
     * @throws LocalizedException
     */
    private function throwException($message = null): void
    {
        $this->messageManager->addErrorMessage($this->getGenericErrorMessage());
        throw new LocalizedException($message ?? $this->getGenericErrorMessage());
    }

    /**
     * @param array $orderItems
     * @return Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function createQuote(array $orderItems): Quote
    {
        $quote = $this->quoteFactory->create();
        $quote->setStore($this->storeManager->getStore());
        $quote->setCurrency();
        $quote->assignCustomer($this->getCustomer());

        foreach ($orderItems as $item) {
            $product = $this->productRepository->getById($item->getProductId());
            $product->setPrice($item->getProductPrice());

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
}
