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
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceService $invoiceService
     * @param Transaction $transaction
     * @param InvoiceSender $invoiceSender
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        InvoiceService $invoiceService,
        Transaction $transaction,
        InvoiceSender $invoiceSender,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
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
}
