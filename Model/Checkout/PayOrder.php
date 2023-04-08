<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Checkout;

use Amwal\Payments\Model\AddressResolver;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\GetAmwalOrderData;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class PayOrder
{

    private CartRepositoryInterface $quoteRepository;
    private CheckoutSession $checkoutSession;
    private InvoiceOrder $invoiceAmwalOrder;
    private GetAmwalOrderData $getAmwalOrderData;
    private Config $config;
    private OrderRepositoryInterface $orderRepository;
    private ManagerInterface $messageManager;
    private OrderPaymentRepositoryInterface $paymentRepository;
    private LoggerInterface $logger;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        CheckoutSession $checkoutSession,
        InvoiceOrder $invoiceAmwalOrder,
        GetAmwalOrderData $getAmwalOrderData,
        Config $config,
        OrderRepositoryInterface $orderRepository,
        ManagerInterface $messageManager,
        OrderPaymentRepositoryInterface $paymentRepository,
        LoggerInterface $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->invoiceAmwalOrder = $invoiceAmwalOrder;
        $this->getAmwalOrderData = $getAmwalOrderData;
        $this->config = $config;
        $this->orderRepository = $orderRepository;
        $this->messageManager = $messageManager;
        $this->paymentRepository = $paymentRepository;
        $this->logger = $logger;
    }

    /**
     * @param int $orderId
     * @param string $amwalOrderId
     * @return bool
     * @throws LocalizedException
     */
    public function execute(int $orderId, string $amwalOrderId): bool
    {
        $order = $this->orderRepository->get($orderId);

        $amwalOrderData = $this->getAmwalOrderData->execute($amwalOrderId);
        if (!$amwalOrderData) {
            $this->logger->error(sprintf('Unable to retrieve Amwal Order Data for order with ID "%s". Amwal Order id: %s', $orderId, $amwalOrderId));
            $this->addError(__('We were unable to retrieve your transaction data.'));
            return false;
        }

        $this->updateCustomerName($order, $amwalOrderData);

        try {
            $quote = $this->quoteRepository->get($order->getQuoteId());
        } catch (NoSuchEntityException $e) {
            $this->logger->error(sprintf('Unable to load Quote for order with ID "%s". Amwal Order id: %s', $orderId, $amwalOrderId));
            $this->addError(__('We were unable to retrieve your order data.'));
            return false;
        }

        $this->addAdditionalPaymentInformation($amwalOrderData, $order);

        $this->updateAddressData($quote, $amwalOrderData);
        $this->updateAddressData($order, $amwalOrderData);

        $order->setState($this->config->getOrderConfirmedStatus());
        $order->setStatus($this->config->getOrderConfirmedStatus());

        $this->checkoutSession->clearHelperData();
        $this->checkoutSession->setLastQuoteId($quote->getId())
            ->setLastSuccessQuoteId($quote->getId());

        $this->checkoutSession->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId())
            ->setLastOrderStatus($order->getStatus());

        $this->orderRepository->save($order);

        $this->invoiceAmwalOrder->execute($order, $amwalOrderData);

        return true;
    }

    /**
     * @param OrderInterface $order
     * @param DataObject $amwalOrderData
     * @return void
     */
    private function updateCustomerName(OrderInterface $order, DataObject $amwalOrderData)
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
}
