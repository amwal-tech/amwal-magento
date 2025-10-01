<?php
namespace Amwal\Payments\Model\Event;

use Amwal\Payments\Model\Event\HandlerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;

/**
 * Handles order.success webhook event
 */
class OrderSuccess implements HandlerInterface
{
    private const WEBHOOK_PROCESSED_FLAG = 'amwal_webhook_processed';
    private const PAYMENT_ID_KEY = 'amwal_payment_id';
    private const PROCESSING_DELAY_SECONDS = 10;

    private const FIELD_MAPPINGS = [
        'discount_amount' => 'discount',
        'grand_total' => 'total_amount',
    ];

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceService $invoiceService
     * @param Transaction $transaction
     * @param InvoiceSender $invoiceSender
     * @param OrderSender $orderSender
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        InvoiceService $invoiceService,
        Transaction $transaction,
        InvoiceSender $invoiceSender,
        OrderSender $orderSender,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
        $this->orderSender = $orderSender;
        $this->logger = $logger;
    }

    /**
     * Execute handler with order and webhook data
     *
     * @param Order $order
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    public function execute(Order $order, array $data)
    {
        $this->logger->info('Processing order.success for order #' . $order->getIncrementId());

        if ($this->shouldSkipProcessing($order)) {
            return;
        }

        $this->addProcessingDelay($order);

        $transactionId = $this->extractTransactionId($data, $order);
        if (!$transactionId) {
            return;
        }

        try {
            $this->validateOrderData($order, $data);
            $this->processPayment($order, $transactionId);
            $this->updateOrderStatus($order, $transactionId);
            $this->sendOrderConfirmationEmail($order);
            $this->processInvoice($order, $transactionId);
            $this->orderRepository->save($order);

            $this->logger->info("Order #{$order->getIncrementId()} processed successfully");
        } catch (\Exception $e) {
            $this->logger->error(
                "Failed to process order #{$order->getIncrementId()}: " . $e->getMessage()
            );
            throw new LocalizedException(__('Failed to process webhook: %1', $e->getMessage()));
        }
    }

    /**
     * Check if order processing should be skipped
     *
     * @param Order $order
     * @return bool
     */
    private function shouldSkipProcessing(Order $order): bool
    {
        // Skip if order is already completed or closed
        if (in_array($order->getState(), [Order::STATE_COMPLETE, Order::STATE_CLOSED])) {
            $this->logger->info(
                "Order #{$order->getIncrementId()} is already in state {$order->getState()}. Skipping."
            );
            return true;
        }

        // Skip if already processed by webhook
        if ($order->getPayment()->getAdditionalInformation(self::WEBHOOK_PROCESSED_FLAG)) {
            $this->logger->info(
                "Order #{$order->getIncrementId()} already processed by webhook. Skipping."
            );
            return true;
        }

        return false;
    }

    /**
     * Add processing delay
     *
     * @param Order $order
     * @return void
     */
    private function addProcessingDelay(Order $order)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        sleep(self::PROCESSING_DELAY_SECONDS);
        $this->logger->info(
            "Delay of " . self::PROCESSING_DELAY_SECONDS . " seconds added for order #{$order->getIncrementId()}"
        );
    }

    /**
     * Extract transaction ID from webhook data
     *
     * @param array $data
     * @param Order $order
     * @return string|null
     */
    private function extractTransactionId(array $data, Order $order): ?string
    {
        $transactionId = $data['data']['id'] ?? null;

        if (!$transactionId) {
            $this->logger->error(
                "No transaction ID found in webhook data for order #{$order->getIncrementId()}"
            );
        }

        return $transactionId;
    }

    /**
     * Process payment information
     *
     * @param Order $order
     * @param string $transactionId
     * @return void
     */
    private function processPayment(Order $order, string $transactionId)
    {
        $payment = $order->getPayment();

        // Update payment information
        $payment->setTransactionId($transactionId);
        $payment->setLastTransId($transactionId);
        $payment->setAdditionalInformation(self::PAYMENT_ID_KEY, $transactionId);
        $payment->setAdditionalInformation(self::WEBHOOK_PROCESSED_FLAG, true);

        // Create payment transaction record
        $this->createPaymentTransaction($payment, $transactionId, $order);

        // Set payment as captured
        $payment->setIsTransactionClosed(true);
        $payment->setShouldCloseParentTransaction(true);
    }

    /**
     * Create payment transaction
     *
     * @param Order\Payment $payment
     * @param string $transactionId
     * @param Order $order
     * @return void
     */
    private function createPaymentTransaction($payment, string $transactionId, Order $order)
    {
        $payment->setParentTransactionId(null);
        $transaction = $payment->addTransaction(PaymentTransaction::TYPE_CAPTURE, null, true);
        $transaction->setIsClosed(true);
        $transaction->setAdditionalInformation(PaymentTransaction::RAW_DETAILS, [
            'Transaction ID' => $transactionId,
            'Payment Method' => $payment->getMethod(),
            'Amount' => $order->getGrandTotal()
        ]);
    }

    /**
     * Update order status
     *
     * @param Order $order
     * @param string $transactionId
     * @return void
     */
    private function updateOrderStatus(Order $order, string $transactionId)
    {
        $order->setState(Order::STATE_PROCESSING);
        $order->setStatus(Order::STATE_PROCESSING);
        $order->setData(self::WEBHOOK_PROCESSED_FLAG, true);

        $order->addCommentToStatusHistory(
            __('[Webhook] Payment successful with transaction ID: %1', $transactionId),
            Order::STATE_PROCESSING,
            true
        );
    }

    /**
     * Send order confirmation email
     *
     * @param Order $order
     * @return void
     */
    private function sendOrderConfirmationEmail(Order $order)
    {
        if ($order->getEmailSent()) {
            return;
        }

        try {
            $this->orderSender->send($order);
            $order->setEmailSent(true);
            $this->logger->info("Order confirmation email sent for order #{$order->getIncrementId()}");
        } catch (\Exception $e) {
            $this->logger->warning(
                "Could not send order confirmation email for order #{$order->getIncrementId()}: " . $e->getMessage()
            );
        }
    }

    /**
     * Process and create invoice
     *
     * @param Order $order
     * @param string $transactionId
     * @return void
     */
    private function processInvoice(Order $order, string $transactionId)
    {
        if (!$order->canInvoice()) {
            return;
        }

        $this->logger->info(
            "[Webhook] Creating invoice for order #{$order->getIncrementId()} with transaction ID: $transactionId"
        );

        try {
            $invoice = $this->createInvoice($order, $transactionId);
            $this->sendInvoiceEmail($invoice, $order);

            $order->addCommentToStatusHistory(
                __('[Webhook] Invoice #%1 created and marked as paid', $invoice->getIncrementId()),
                false,
                true
            );

            $this->logger->info(
                "Invoice #{$invoice->getIncrementId()} created successfully for order #{$order->getIncrementId()}"
            );
        } catch (\Exception $e) {
            $this->logger->error("Failed to create invoice: " . $e->getMessage());
            $order->addCommentToStatusHistory(
                __('[Webhook] Failed to create invoice: %1', $e->getMessage()),
                false,
                true
            );
        }
    }

    /**
     * Create invoice
     *
     * @param Order $order
     * @param string $transactionId
     * @return Order\Invoice
     */
    private function createInvoice(Order $order, string $transactionId)
    {
        $invoice = $this->invoiceService->prepareInvoice($order);
        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->pay();
        $invoice->setTransactionId($transactionId);

        $this->transaction->addObject($invoice)
            ->addObject($order)
            ->save();

        return $invoice;
    }

    /**
     * Send invoice email
     *
     * @param Order\Invoice $invoice
     * @param Order $order
     * @return void
     */
    private function sendInvoiceEmail($invoice, Order $order)
    {
        try {
            $this->invoiceSender->send($invoice);
        } catch (\Exception $e) {
            $this->logger->warning(
                "Could not send invoice email for order #{$order->getIncrementId()}: " . $e->getMessage()
            );
        }
    }

    /**
     * Validate order data against Amwal data
     *
     * @param Order $order
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    private function validateOrderData(Order $order, array $data)
    {
        $amwalData = $data['data'] ?? [];

        foreach (self::FIELD_MAPPINGS as $orderField => $amwalField) {
            $this->validateField($order, $orderField, $amwalField, $amwalData);
        }

        $this->logger->info("All field validation passed for order #{$order->getIncrementId()}");
    }

    /**
     * Validate single field
     *
     * @param Order $order
     * @param string $orderField
     * @param string $amwalField
     * @param array $amwalData
     * @return void
     * @throws LocalizedException
     */
    private function validateField(Order $order, string $orderField, string $amwalField, array $amwalData)
    {
        $orderValue = $order->getData($orderField);
        $amwalValue = $amwalData[$amwalField] ?? null;

        // Check if value exists in webhook data
        if ($amwalValue === null) {
            $errorMessage = "No {$amwalField} found in webhook data for order #{$order->getIncrementId()}";
            $this->logger->error($errorMessage);
            throw new LocalizedException(__($errorMessage));
        }

        // Normalize values for comparison
        $orderValue = $this->normalizeValue($orderValue, $orderField);
        $amwalValue = $this->normalizeValue($amwalValue, $orderField);

        // Compare values
        if ($orderValue != $amwalValue) {
            $errorMessage = sprintf(
                'Order (%s) %s does not match Amwal Order %s (%s != %s)',
                $order->getIncrementId(),
                $orderField,
                $amwalField,
                $orderValue,
                $amwalValue
            );

            $this->logger->error($errorMessage);
            throw new LocalizedException(__($errorMessage));
        }
    }

    /**
     * Normalize value for comparison
     *
     * @param mixed $value
     * @param string $fieldName
     * @return float
     */
    private function normalizeValue($value, string $fieldName): float
    {
        $normalizedValue = $this->roundValue((float)$value);

        // For discount_amount, use absolute value
        // Magento stores discounts as negative, Amwal might send as positive
        if ($fieldName === 'discount_amount') {
            $normalizedValue = abs($normalizedValue);
        }

        return $normalizedValue;
    }

    /**
     * Round value to 2 decimal places for comparison
     *
     * @param float $value
     * @return float
     */
    private function roundValue(float $value): float
    {
        return round($value, 2);
    }
}
