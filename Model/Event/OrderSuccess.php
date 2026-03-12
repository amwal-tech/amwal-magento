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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute(Order $order, array $data)
    {
        $this->logger->info('Processing order.success for order #' . $order->getIncrementId());

        // Reload order from DB to get the freshest state (another process may have already updated it)
        $order = $this->orderRepository->get($order->getId());

        // Don't process already completed/closed/processing orders
        if (in_array($order->getState(), [Order::STATE_COMPLETE, Order::STATE_CLOSED, Order::STATE_PROCESSING])) {
            $this->logger->info("Order #{$order->getIncrementId()} is already in state {$order->getState()}. Skipping webhook.");
            return;
        }

        // Check if already processed via the sales_order column (set by PayOrder, cron, or previous webhook)
        if ($order->getData('amwal_webhook_processed')) {
            $this->logger->info("Order #{$order->getIncrementId()} already has amwal_webhook_processed flag. Skipping.");
            return;
        }

        // Check if already processed via payment additional info (set by previous webhook)
        if ($order->getPayment()->getAdditionalInformation('amwal_webhook_processed')) {
            $this->logger->info("Order #{$order->getIncrementId()} already processed by webhook (payment flag). Skipping.");
            return;
        }

        // Check if order already has invoices (created by PayOrder/InvoiceOrder or cron)
        if ($order->hasInvoices()) {
            $this->logger->info("Order #{$order->getIncrementId()} already has invoices. Skipping webhook processing.");
            return;
        }

        // Atomically claim the processing flag to prevent race conditions between concurrent webhooks
        if (!$this->claimWebhookProcessing((int)$order->getId())) {
            $this->logger->info("Order #{$order->getIncrementId()} webhook processing already claimed by another process. Skipping.");
            return;
        }

        $this->logger->info("Successfully claimed webhook processing for order #{$order->getIncrementId()}");

        // Validate order data before processing
        $this->validateOrderData($order, $data);

        // Get transaction details
        $transactionId = $data['data']['id'] ?? null;
        if (!$transactionId) {
            $this->logger->error("No transaction ID found in webhook data for order #{$order->getIncrementId()}");
            return;
        }

        // Update payment information
        $payment = $order->getPayment();
        $payment->setTransactionId($transactionId);
        $payment->setLastTransId($transactionId);
        $payment->setAdditionalInformation('amwal_payment_id', $transactionId);
        $payment->setAdditionalInformation('amwal_webhook_processed', true);

        // Create payment transaction record
        $payment->setParentTransactionId(null);
        $transaction = $payment->addTransaction(PaymentTransaction::TYPE_CAPTURE, null, true);
        $transaction->setIsClosed(true);
        $transaction->setAdditionalInformation(PaymentTransaction::RAW_DETAILS, [
            'Transaction ID' => $transactionId,
            'Payment Method' => $order->getPayment()->getMethod(),
            'Amount' => $order->getGrandTotal()
        ]);

        // Set payment as captured
        $payment->setIsTransactionClosed(true);
        $payment->setShouldCloseParentTransaction(true);

        // Set order state and status
        $orderState = Order::STATE_PROCESSING;
        $orderStatus = 'processing';

        $order->setState(Order::STATE_PROCESSING);
        $order->setStatus(Order::STATE_PROCESSING);

        // Check if order confirmation email was already sent to prevent duplicates
        $emailAlreadySent = $order->getEmailSent() || $order->getIsCustomerNotified();

        if (!$emailAlreadySent) {
            $this->logger->info("Sending order confirmation email for order #{$order->getIncrementId()} via webhook");
            $order->setSendEmail(true);
            $this->orderNotifier->notify($order);
            $order->setIsCustomerNotified(true);
        } else {
            $this->logger->info("Order confirmation email already sent for order #{$order->getIncrementId()}, skipping duplicate");
            $order->setSendEmail(false);
        }

        // Set custom field for webhook processing (already claimed via SQL, but keep in sync with ORM)
        $order->setData('amwal_webhook_processed', true);

        // Add comment to order history
        $order->addCommentToStatusHistory(
            __('[Webhook] Payment successful with transaction ID: %1', $transactionId),
            $orderStatus,
            true
        );

        // Create invoice if the order doesn't have one already
        if ($order->canInvoice()) {
            $this->logger->info("[Webhook] Creating invoice for order #{$order->getIncrementId()} with transaction ID: $transactionId");

            try {
                // Create the invoice
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                // Register and explicitly mark as paid
                $invoice->register();
                $invoice->pay();

                // Set invoice transaction ID
                $invoice->setTransactionId($transactionId);
                // Save the invoice and order
                $this->transaction->addObject($invoice)
                    ->addObject($order)
                    ->save();

                // Send invoice email (wrap in try-catch to not fail if email fails)
                try {
                    $this->invoiceSender->send($invoice);
                } catch (\Exception $emailException) {
                    $this->logger->warning("Could not send invoice email: " . $emailException->getMessage());
                }

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
        } else {
            $this->logger->info("Order #{$order->getIncrementId()} cannot be invoiced (already invoiced or not invoiceable). Skipping invoice creation.");
        }

        // Save the order
        $this->orderRepository->save($order);
        $this->logger->info("Order #{$order->getIncrementId()} updated to {$orderState} status");
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
