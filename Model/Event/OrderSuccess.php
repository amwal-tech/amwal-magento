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

        // Check if the quote still exists and is not cleaned up
        try {
            $quoteId = $order->getQuoteId();
            if ($quoteId) {
                $quote = $this->quoteRepository->get($quoteId);
                $this->logger->info("Quote #{$quoteId} found for order #{$order->getIncrementId()} with status: {$quote->getIsActive()}");
                if ($quote && $quote->getIsActive()) {
                    $this->logger->info("Quote #{$quoteId} found but inactive for order #{$order->getIncrementId()}");
                    if (!$this->isOrderReadyForProcessing($order)) {
                        $this->logger->info("Order #{$order->getIncrementId()} not ready for processing yet. Will be handled by subsequent webhook or cron.");
                        return;
                    }
                }
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // Quote already removed, no need for delay
            $this->logger->info("Quote already cleaned up for order #{$order->getIncrementId()}, proceeding with processing");
        }

        // Don't process already completed/closed orders
        if ($order->getState() === Order::STATE_COMPLETE || $order->getState() === Order::STATE_CLOSED) {
            $this->logger->info("Order #{$order->getIncrementId()} is already in state {$order->getState()}. Skipping.");
            return;
        }

        // Get transaction details
        $transactionId = $data['data']['id'] ?? null;

        // Update payment information
        $payment = $order->getPayment();
        $payment->setTransactionId($transactionId);
        $payment->setLastTransId($transactionId);
        $payment->setAdditionalInformation('amwal_payment_id', $transactionId);

        // Create payment transaction record
        $payment->setParentTransactionId(null);
        $transaction = $payment->addTransaction(PaymentTransaction::TYPE_CAPTURE, null, true);
        $transaction->setIsClosed(true);
        $transaction->setAdditionalInformation(PaymentTransaction::RAW_DETAILS, [
            'Transaction ID' => $transactionId,
            'Payment Method' => 'amwal_payments',
            'Amount' => $order->getGrandTotal()
        ]);

        // Set payment as captured
        $payment->setIsTransactionClosed(true);
        $payment->setShouldCloseParentTransaction(true);

        // Set order state and status
        $orderState = Order::STATE_PROCESSING;
        $orderStatus = 'processing';

        $order->setState($orderState);
        $order->setStatus($orderStatus);

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
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->register();

                // Set invoice transaction ID
                $invoice->setTransactionId($transactionId);

                // Save the invoice
                $this->transaction->addObject($invoice)
                    ->addObject($transaction)
                    ->addObject($order)
                    ->save();

                // Send invoice email
                $this->invoiceSender->send($invoice);

                $order->addCommentToStatusHistory(
                    __('[Webhook] Invoice #%1 created', $invoice->getIncrementId()),
                    false,
                    true
                );
            } catch (\Exception $e) {
                $this->logger->error("Failed to create invoice: " . $e->getMessage());
                $order->addCommentToStatusHistory(
                    __('[Webhook] Failed to create invoice: %1', $e->getMessage()),
                    false,
                    true
                );
            }
        } else {
            $this->logger->info("Order #{$order->getIncrementId()} cannot be invoiced in its current state");
        }

        // Save the order
        $this->orderRepository->save($order);

        $this->logger->info("Order #{$order->getIncrementId()} updated to {$orderState} status");
    }

    /**
     * Check if order is ready for processing
     *
     * This method determines if an order is ready for webhook processing based on
     * various conditions. This replaces the need for a blocking sleep() call.
     *
     * @param Order $order
     * @return bool
     */
    private function isOrderReadyForProcessing(Order $order)
    {
        // Check if order has any front-end locks or is in a transitional state

        // 1. Check if the order was just created (within the last few seconds)
        // This helps avoid race conditions with the frontend order placement
        $createdAt = strtotime($order->getCreatedAt());
        $currentTime = time();
        $timeDifference = $currentTime - $createdAt;

        if ($timeDifference < 10) {
            $this->logger->info("Order #{$order->getIncrementId()} created less than 10 seconds ago. Waiting for frontend to finish processing.");
            // Order was created less than 10 seconds ago
            // Too early to process the webhook, let frontend finish first
            return false;
        }

        // 2. Check order status to see if it's in a state that should be processed
        // If it's already been processed by the frontend, we can proceed
        if ($order->getState() != Order::STATE_NEW &&
            $order->getState() != Order::STATE_PENDING_PAYMENT) {
            $this->logger->info("Order #{$order->getIncrementId()} is in state {$order->getState()}. Proceeding with processing.");
            // Order has already been updated by another process
            return false;
        }
        return true;
    }
}
