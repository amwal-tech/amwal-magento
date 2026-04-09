<?php
namespace Amwal\Payments\Model\Event;

use Amwal\Payments\Model\Event\HandlerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderNotifier;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Handles order.success webhook event
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderSuccess implements HandlerInterface
{
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var OrderNotifier
     */
    private OrderNotifier $orderNotifier;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    private const FIELD_MAPPINGS = [
        'discount_amount' => 'discount',
        'grand_total' => 'total_amount',
    ];

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceService $invoiceService
     * @param Transaction $transaction
     * @param InvoiceSender $invoiceSender
     * @param LoggerInterface $logger
     * @param CartRepositoryInterface $quoteRepository
     * @param OrderNotifier $orderNotifier
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        InvoiceService $invoiceService,
        Transaction $transaction,
        InvoiceSender $invoiceSender,
        LoggerInterface $logger,
        CartRepositoryInterface $quoteRepository,
        OrderNotifier $orderNotifier,
        ResourceConnection $resourceConnection
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
        $this->orderNotifier = $orderNotifier;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Atomically claim the webhook processing flag for this order.
     * Returns true if this process successfully claimed it (no other process has).
     *
     * @param int $orderId
     * @return bool
     */
    private function claimWebhookProcessing(int $orderId): bool
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('sales_order');

        $affectedRows = $connection->update(
            $tableName,
            ['amwal_webhook_processed' => 1],
            ['entity_id = ?' => $orderId, 'amwal_webhook_processed = ?' => 0]
        );

        return $affectedRows > 0;
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

        // Reload order from DB to get the freshest state (another process may have already updated it)
        $order = $this->orderRepository->get($order->getId());

        if ($this->shouldSkipProcessing($order)) {
            return;
        }

        // Validate order data before processing
        $this->validateOrderData($order, $data);

        // Get transaction details
        $transactionId = $data['data']['id'] ?? null;
        if (!$transactionId) {
            $this->logger->error("No transaction ID found in webhook data for order #{$order->getIncrementId()}");
            return;
        }

        $this->setupPaymentTransaction($order, $transactionId);

        $order->setState(Order::STATE_PROCESSING);
        $order->setStatus(Order::STATE_PROCESSING);

        $this->sendOrderConfirmationEmail($order);

        $order->setData('amwal_webhook_processed', true);

        $order->addCommentToStatusHistory(
            __('[Webhook] Payment successful with transaction ID: %1', $transactionId),
            'processing',
            true
        );

        $this->createInvoice($order, $transactionId);

        // Save the order
        $this->orderRepository->save($order);
        $this->logger->info("Order #{$order->getIncrementId()} updated to " . Order::STATE_PROCESSING . " status");
    }

    /**
     * Check whether the order should be skipped for processing.
     *
     * @param Order $order
     * @return bool
     */
    private function shouldSkipProcessing(Order $order): bool
    {
        $incrementId = $order->getIncrementId();

        if (in_array($order->getState(), [Order::STATE_COMPLETE, Order::STATE_CLOSED, Order::STATE_PROCESSING])) {
            $this->logger->info("Order #{$incrementId} is already in state {$order->getState()}. Skipping webhook.");
            return true;
        }

        if ($order->getData('amwal_webhook_processed')) {
            $this->logger->info("Order #{$incrementId} already has amwal_webhook_processed flag. Skipping.");
            return true;
        }

        if ($order->getPayment()->getAdditionalInformation('amwal_webhook_processed')) {
            $this->logger->info("Order #{$incrementId} already processed by webhook (payment flag). Skipping.");
            return true;
        }

        if ($order->hasInvoices()) {
            $this->logger->info("Order #{$incrementId} already has invoices. Skipping webhook processing.");
            return true;
        }

        if (!$this->claimWebhookProcessing((int)$order->getId())) {
            $this->logger->info("Order #{$incrementId} webhook processing already claimed by another process. Skipping.");
            return true;
        }

        $this->logger->info("Successfully claimed webhook processing for order #{$incrementId}");
        return false;
    }

    /**
     * Set up payment transaction details.
     *
     * @param Order $order
     * @param string $transactionId
     * @return void
     */
    private function setupPaymentTransaction(Order $order, string $transactionId): void
    {
        $payment = $order->getPayment();
        $payment->setTransactionId($transactionId);
        $payment->setLastTransId($transactionId);
        $payment->setAdditionalInformation('amwal_payment_id', $transactionId);
        $payment->setAdditionalInformation('amwal_webhook_processed', true);

        $payment->setParentTransactionId(null);
        $transaction = $payment->addTransaction(PaymentTransaction::TYPE_CAPTURE, null, true);
        $transaction->setIsClosed(true);
        $transaction->setAdditionalInformation(PaymentTransaction::RAW_DETAILS, [
            'Transaction ID' => $transactionId,
            'Payment Method' => $payment->getMethod(),
            'Amount' => $order->getGrandTotal()
        ]);

        $payment->setIsTransactionClosed(true);
        $payment->setShouldCloseParentTransaction(true);
    }

    /**
     * Send order confirmation email if not already sent.
     *
     * @param Order $order
     * @return void
     */
    private function sendOrderConfirmationEmail(Order $order): void
    {
        $emailAlreadySent = $order->getEmailSent() || $order->getIsCustomerNotified();

        if ($emailAlreadySent) {
            $this->logger->info("Order confirmation email already sent for order #{$order->getIncrementId()}, skipping duplicate");
            $order->setSendEmail(false);
            return;
        }

        $this->logger->info("Sending order confirmation email for order #{$order->getIncrementId()} via webhook");
        $order->setSendEmail(true);
        $this->orderNotifier->notify($order);
        $order->setIsCustomerNotified(true);
    }

    /**
     * Create invoice for the order if possible.
     *
     * @param Order $order
     * @param string $transactionId
     * @return void
     */
    private function createInvoice(Order $order, string $transactionId): void
    {
        if (!$order->canInvoice()) {
            $this->logger->info("Order #{$order->getIncrementId()} cannot be invoiced (already invoiced or not invoiceable). Skipping invoice creation.");
            return;
        }

        $this->logger->info("[Webhook] Creating invoice for order #{$order->getIncrementId()} with transaction ID: $transactionId");

        try {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
            $invoice->register();
            $invoice->pay();
            $invoice->setTransactionId($transactionId);

            $this->transaction->addObject($invoice)
                ->addObject($order)
                ->save();

            $this->sendInvoiceEmail($invoice);

            $order->addCommentToStatusHistory(
                __('[Webhook] Invoice #%1 created and marked as paid', $invoice->getIncrementId()),
                false,
                true
            );

            $this->logger->info("Invoice #{$invoice->getIncrementId()} created successfully for order #{$order->getIncrementId()}");
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
     * Send invoice email, suppressing exceptions.
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return void
     */
    private function sendInvoiceEmail($invoice): void
    {
        try {
            $this->invoiceSender->send($invoice);
        } catch (\Exception $emailException) {
            $this->logger->warning("Could not send invoice email: " . $emailException->getMessage());
        }
    }

    /**
     * Validate order data against Amwal data
     *
     * @param Order $order
     * @param array $data
     * @return void
     */
    private function validateOrderData(Order $order, array $data)
    {
        $amwalData = $data['data'] ?? [];

        foreach (self::FIELD_MAPPINGS as $orderField => $amwalField) {
            $orderValue = $order->getData($orderField);
            $amwalValue = $amwalData[$amwalField] ?? null;

            // Handle null values
            if ($amwalValue === null) {
                $this->logger->error("No {$amwalField} found in webhook data for order #{$order->getIncrementId()}");
                return;
            }

            // Special handling for monetary values that need rounding
            if (in_array($orderField, ['grand_total', 'discount_amount'])) {
                $orderValue = $this->roundValue((float)$orderValue);
                $amwalValue = $this->roundValue((float)$amwalValue);
            }

            // For discount_amount, compare absolute values
            // Magento stores discounts as negative, Amwal might send as positive
            if ($orderField === 'discount_amount') {
                $orderValue = abs($orderValue);
                $amwalValue = abs($amwalValue);
            }

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
                return;
            }
        }

        $this->logger->info("All field validation passed for order #{$order->getIncrementId()}");
    }

    /**
     * Round value to 2 decimal places for comparison
     *
     * @param float $value
     * @return float
     */
    private function roundValue($value)
    {
        return round((float)$value, 2);
    }
}
