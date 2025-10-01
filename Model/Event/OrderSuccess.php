<?php
namespace Amwal\Payments\Model\Event;

use Amwal\Payments\Model\Event\HandlerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Handles order.success webhook event
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
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        InvoiceService $invoiceService,
        Transaction $transaction,
        InvoiceSender $invoiceSender,
        LoggerInterface $logger,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
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

        // Don't process already completed/closed orders
        if ($order->getState() === Order::STATE_COMPLETE || $order->getState() === Order::STATE_CLOSED) {
            $this->logger->info("Order #{$order->getIncrementId()} is already in state {$order->getState()}. Skipping.");
            return;
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        sleep(10);
        $this->logger->info("Delay of 10 seconds added for order #{$order->getIncrementId()}");

        // Check if already processed to prevent duplicate processing
        if ($order->getPayment()->getAdditionalInformation('amwal_webhook_processed')) {
            $this->logger->info("Order #{$order->getIncrementId()} already processed by webhook. Skipping.");
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
        $order->setEmailSent(true);

        $order->setState(Order::STATE_PROCESSING);
        $order->setStatus(Order::STATE_PROCESSING);

        // Set custom field for webhook processing
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
