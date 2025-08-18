<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Data;

use Amwal\Payments\Plugin\Sentry\SentryExceptionReport;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\CurrencyConverter;
use Amwal\Payments\Model\GetAmwalOrderData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderNotifier;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Amwal\Payments\Model\Checkout\InvoiceOrder;
use Psr\Log\LoggerInterface;
use Amwal\Payments\Model\AmwalClientFactory;
use Magento\Framework\DataObject;
use RuntimeException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderUpdate
{
    private OrderRepositoryInterface $orderRepository;
    private StoreManagerInterface $storeManager;
    private GetAmwalOrderData $getAmwalOrderData;
    private Config $config;
    private OrderNotifier $orderNotifier;
    private TransportInterfaceFactory $transportFactory;
    private MessageInterface $message;
    private ScopeConfigInterface $scopeConfig;
    private InvoiceOrder $invoiceAmwalOrder;
    private LoggerInterface $logger;
    private AmwalClientFactory $amwalClientFactory;
    private SentryExceptionReport $sentryExceptionReport;
    private CurrencyConverter $currencyConverter;

    private const FIELD_MAPPINGS = [
        'amwal_order_id' => 'id',
        'ref_id' => 'ref_id',
    ];

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param StoreManagerInterface $storeManager
     * @param GetAmwalOrderData $getAmwalOrderData
     * @param Config $config
     * @param OrderNotifier $orderNotifier
     * @param TransportInterfaceFactory $transportFactory
     * @param MessageInterface $message
     * @param ScopeConfigInterface $scopeConfig
     * @param InvoiceOrder $invoiceAmwalOrder
     * @param LoggerInterface $logger
     * @param AmwalClientFactory $amwalClientFactory
     * @param SentryExceptionReport $sentryExceptionReport
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        OrderRepositoryInterface  $orderRepository,
        StoreManagerInterface     $storeManager,
        GetAmwalOrderData         $getAmwalOrderData,
        Config                    $config,
        OrderNotifier             $orderNotifier,
        TransportInterfaceFactory $transportFactory,
        MessageInterface          $message,
        ScopeConfigInterface      $scopeConfig,
        InvoiceOrder              $invoiceAmwalOrder,
        LoggerInterface           $logger,
        AmwalClientFactory        $amwalClientFactory,
        SentryExceptionReport     $sentryExceptionReport,
        CurrencyConverter         $currencyConverter
    ) {
        $this->orderRepository = $orderRepository;
        $this->storeManager = $storeManager;
        $this->getAmwalOrderData = $getAmwalOrderData;
        $this->config = $config;
        $this->orderNotifier = $orderNotifier;
        $this->transportFactory = $transportFactory;
        $this->message = $message;
        $this->scopeConfig = $scopeConfig;
        $this->invoiceAmwalOrder = $invoiceAmwalOrder;
        $this->logger = $logger;
        $this->amwalClientFactory = $amwalClientFactory;
        $this->sentryExceptionReport = $sentryExceptionReport;
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * Updates the order based on specified trigger and conditions.
     *
     * @param Order $order Order to be updated.
     * @param string $trigger Type of trigger initiating the update.
     * @param bool $sendAdminEmail Indicates if an admin email should be sent.
     * @return DataObject|false Returns Amwal order data on success, or false on failure.
     * @throws Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function update(Order $order, string $trigger, bool $sendAdminEmail = true)
    {
        try {
            $amwalOrderId = $order->getAmwalOrderId();
            if (!$amwalOrderId) {
                throw new RuntimeException(sprintf('Order %s does not have an Amwal Order ID', $order->getIncrementId()));
            }
            if ($order->getIsAmwalOrderCanceled()) {
                throw new RuntimeException(sprintf('Skipping Order %s as it was canceled because the payment was retried.', $amwalOrderId));
            }
            $amwalOrderData = $this->getAmwalOrderData->execute($amwalOrderId);
            if (!$amwalOrderData) {
                throw new RuntimeException(sprintf('Skipping Order %s as it does not exist in Amwal', $amwalOrderId));
            }
            if (!$this->dataValidation($order, $amwalOrderData)) {
                return false;
            }
            if (!$this->isPayValid($order)) {
                return false;
            }

            $status = $amwalOrderData->getStatus();
            if ($trigger === 'PendingOrdersUpdate') {
                $historyComment = __('Successfully completed Amwal payment with transaction ID %1 By Pending Orders Cron Job', $amwalOrderId);
            } elseif ($trigger === 'CanceledOrdersUpdate') {
                $historyComment = __('Successfully completed Amwal payment with transaction ID %1 By Canceled Orders Cron Job', $amwalOrderId);
            } elseif ($trigger === 'AmwalOrderDetails') {
                $historyComment = __('Order status updated to (%1) by Amwal Payments webhook', $status);
            } elseif ($trigger === 'PayOrder') {
                $historyComment = __('Successfully completed Amwal payment with transaction ID: %1', $amwalOrderId);
            } else {
                $historyComment = __('Order status updated to (%1) by Amwal Payments', $status);
            }

            // Update order status
            if ($this->verifyStatus($status)) {
                $order->setState($this->config->getOrderConfirmedStatus());
                $order->setStatus($this->config->getOrderConfirmedStatus());
                $order->addCommentToStatusHistory($historyComment);
                $order->setTotalPaid($order->getGrandTotal());
                $this->setOrderUrl($order, $order->getAmwalOrderId());

                // Send customer email
                $this->sendCustomerEmail($order);

                if ($sendAdminEmail) {
                    // Send admin email
                    $this->sendAdminEmail($order);
                }
            } elseif ($status === 'fail' && $order->getState() !== Order::STATE_CANCELED) {
                $order->setState(Order::STATE_CANCELED);
                $order->setStatus(Order::STATE_CANCELED);
                $order->setIsAmwalOrderCanceled(true);
                $order->addCommentToStatusHistory('Amwal Transaction Id: ' . $amwalOrderData->getId() . ' has been pending, status: (' . $status . ') and order has been canceled.');
                $order->addCommentToStatusHistory('Amwal Transaction Id: ' . $amwalOrderData->getId() . ' Amwal failure reason: ' . $amwalOrderData->getFailureReason());
            }

            // Save the updated order
            $this->orderRepository->save($order);

            if (!$order->hasInvoices() && $this->verifyStatus($status)) {
                $this->invoiceAmwalOrder->execute($order, $amwalOrderData);
            }

            return $this->verifyStatus($status) ? $amwalOrderData : false;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->sentryExceptionReport->report($e);
            throw $e;
        }
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function isPayValid(OrderInterface $order): bool
    {
        $orderState = $order->getState();
        $defaultOrderStatus = $this->config->getOrderConfirmedStatus();

        if ($orderState === $defaultOrderStatus) {
            return false;
        }
        $validStates = ['pending_payment', 'canceled'];
        if (!in_array($orderState, $validStates)) {
            throw new RuntimeException(sprintf('Order (%s) is not in a valid state to be updated (%s)', $order->getIncrementId(), $orderState));
        }
        return true;
    }

    /**
     * @param $order
     *
     * @return void
     * @throws MailException
     */
    private function sendCustomerEmail($order): void
    {
        if ($this->config->isOrderStatusChangedCustomerEmailEnabled()) {
            $order->setSendEmail(true);
            $this->orderNotifier->notify($order);
            $order->setIsCustomerNotified(true);
        }
    }

    /**
     * @param OrderInterface $order
     * @param string $subject
     * @param string|null $message
     *
     * @throws MailException
     */
    private function sendAdminEmail(OrderInterface $order, string $subject = 'Order Status Changed by Amwal Payment', ?string $message = null): void
    {
        if ($this->config->isOrderStatusChangedAdminEmailEnabled()) {
            // Get store email
            $senderEmail = $this->scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE);
            $mailContent =  $message ?? __('Order (%1) status has been changed to (%2) by Amwal Payment', $order->getIncrementId(), $order->getStatus());
            // Set email content and type
            $this->message->setBody((string) $mailContent);
            $this->message->setFrom($senderEmail);
            $this->message->addTo($senderEmail);
            $this->message->setSubject($subject);
            $this->message->setMessageType(MessageInterface::TYPE_TEXT);

            // Create transport and send the email
            $transport = $this->transportFactory->create(['message' => clone $this->message]);
            $transport->sendMessage();
        }
    }

    /**
     * @param OrderInterface $order
     * @param string $amwalOrderId
     *
     * @throws NoSuchEntityException
     */
    private function setOrderUrl(OrderInterface $order, string $amwalOrderId): void
    {
        $amwalClient = $this->amwalClientFactory->create();
        $orderDetails = [];
        $orderDetails['order_url'] = $this->getOrderUrl($order);
        try {
            $amwalClient->post(
                'transactions/' . $amwalOrderId . '/set_order_details',
                [
                    RequestOptions::JSON => $orderDetails
                ]
            );
        } catch (GuzzleException $e) {
            $message = sprintf(
                'Unable to set Order details in Amwal for order with ID "%s". Exception: %s',
                $amwalOrderId,
                $e->getMessage()
            );
            throw new RuntimeException($message);
        }
    }

    /**
     * @param OrderInterface $order
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getOrderUrl(OrderInterface $order): string
    {
        return $this->storeManager->getStore()->getBaseUrl() . 'sales/order/view/order_id/' . $order->getEntityId();
    }

    /**
     * Validates the order data.
     *
     * @param Order $order
     * @param DataObject $amwalOrderData
     *
     * @return bool
     * @throws MailException
     */
    public function dataValidation(Order $order, DataObject $amwalOrderData): bool
    {
        $subject = (string)__('Order (%1) needs Attention', $order->getIncrementId());
        if ($this->roundValue($order->getGrandTotal()) !== $this->roundValue($amwalOrderData->getTotalAmount())) {
            $message = $this->dataValidationMessage(
                $order->getIncrementId(),
                'grand_total',
                'total_amount',
                (string)$order->getGrandTotal(),
                (string)$amwalOrderData->getTotalAmount()
            );
            $this->sendAdminEmail($order, $subject, $message);
            throw new RuntimeException(sprintf('Order (%s) %s does not match Amwal Order %s (%s != %s)', $order->getIncrementId(), 'grand_total', 'total_amount', $order->getGrandTotal(), $amwalOrderData->getTotalAmount()));
        }
        if (abs($this->roundValue($order->getDiscountAmount())) !== $this->roundValue($amwalOrderData->getDiscount())) {
            $message = $this->dataValidationMessage(
                $order->getIncrementId(),
                'discount_amount',
                'discount',
                (string)$order->getGrandTotal(),
                (string)$amwalOrderData->getTotalAmount()
            );
            $this->sendAdminEmail($order, $subject, $message);
            throw new RuntimeException(sprintf('Order (%s) %s does not match Amwal Order %s (%s != %s)', $order->getIncrementId(), 'discount_amount', 'discount', $order->getDiscountAmount(), $amwalOrderData->getDiscount()));
        }
        foreach (self::FIELD_MAPPINGS as $orderMethod => $amwalMethod) {
            $orderValue = $order->getData($orderMethod);
            $amwalValue = $amwalOrderData->getData($amwalMethod);
            if ($orderValue != $amwalValue) {
                $message = $this->dataValidationMessage(
                    $order->getIncrementId(),
                    $orderMethod,
                    $amwalMethod,
                    $orderValue,
                    $amwalValue
                );
                $this->sendAdminEmail($order, $subject, $message);
                throw new RuntimeException(sprintf('Order (%s) %s does not match Amwal Order %s (%s != %s)', $order->getIncrementId(), $orderMethod, $amwalMethod, $orderValue, $amwalValue));
            }
        }
        return true;
    }

    /**
     * @param string $orderId
     * @param string $orderMethod
     * @param string $amwalMethod
     * @param float|string $orderValue
     * @param string $amwalValue
     * @return string
     */
    private function dataValidationMessage(string $orderId, string $orderMethod, string $amwalMethod, $orderValue, string $amwalValue): string
    {
        return (string) __('Order (%1) Needs Attention, Please check Amwal Order Details in the Sales Order View Page..., Note: Order (%2) %3 does not match Amwal Order %4 (%5 != %6)', $orderId, $orderId, $orderMethod, $amwalMethod, $orderValue, $amwalValue);
    }

    /**
     * @param string|null $status
     *
     * @return bool
     */
    private function verifyStatus(?string $status): bool
    {
        return $status === 'success';
    }

    /**
     * Rounds a value to a specified precision.
     *
     * @param float $value The value to be rounded.
     * @param int $precision The number of decimal places to round to.
     * @return float The rounded value.
     */
    private function roundValue($value, $precision = 2)
    {
        return round((float)$value, $precision);
    }
}
