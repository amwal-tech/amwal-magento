<?php
namespace Amwal\Payments\Model\Event;

use Amwal\Payments\Model\Event\HandlerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Framework\Lock\LockManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Handles order.updated webhook event (refunds)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderUpdated implements HandlerInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CreditmemoFactory
     */
    private $creditmemoFactory;

    /**
     * @var CreditmemoManagementInterface
     */
    private $creditmemoManagement;

    /**
     * @var CreditmemoSender
     */
    private $creditmemoSender;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var LockManagerInterface
     */
    private $lockManager;

    /**
     * Amwal statuses that indicate a refund
     */
    private const REFUND_STATUSES = ['refunded', 'partially_refunded'];

    /**
     * Lock wait timeout in seconds
     */
    private const LOCK_WAIT_TIMEOUT = 10;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param CreditmemoFactory $creditmemoFactory
     * @param CreditmemoManagementInterface $creditmemoManagement
     * @param CreditmemoSender $creditmemoSender
     * @param LoggerInterface $logger
     * @param LockManagerInterface $lockManager
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CreditmemoFactory $creditmemoFactory,
        CreditmemoManagementInterface $creditmemoManagement,
        CreditmemoSender $creditmemoSender,
        LoggerInterface $logger,
        LockManagerInterface $lockManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoManagement = $creditmemoManagement;
        $this->creditmemoSender = $creditmemoSender;
        $this->logger = $logger;
        $this->lockManager = $lockManager;
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
        $this->logger->info('Processing order.updated for order #' . $order->getIncrementId());

        $amwalData = $data['data'] ?? [];
        $amwalStatus = $amwalData['status'] ?? null;

        $this->logger->info("Order #{$order->getIncrementId()} - Amwal status: " . ($amwalStatus ?? 'null'));

        // Check if this is a refund event
        if (!$this->isRefundEvent($amwalStatus, $amwalData)) {
            $this->logger->info("Order #{$order->getIncrementId()} - Not a refund event (status: {$amwalStatus}). Skipping.");
            return;
        }

        // Use advisory lock to prevent duplicate processing from webhook retries
        $lockName = 'amwal_refund_order_' . $order->getId();

        if (!$this->lockManager->lock($lockName, self::LOCK_WAIT_TIMEOUT)) {
            $this->logger->info("Order #{$order->getIncrementId()} - Could not acquire lock. Another refund is being processed. Skipping.");
            return;
        }

        try {
            // Reload order inside the lock to get the freshest state
            $order = $this->orderRepository->get($order->getId());

            if ($this->shouldSkipProcessing($order, $amwalData)) {
                return;
            }

            $this->processRefund($order, $amwalData);
        } catch (\Exception $e) {
            $this->logger->critical(
                "Error processing order.updated (refund) webhook for order #{$order->getIncrementId()}: " . $e->getMessage(),
                ['exception' => $e]
            );
            throw new LocalizedException(__('Error processing refund: %1', $e->getMessage()));
        } finally {
            $this->lockManager->unlock($lockName);
        }
    }

    /**
     * Determine if the webhook event represents a refund.
     *
     * @param string|null $amwalStatus
     * @param array $amwalData
     * @return bool
     */
    private function isRefundEvent(?string $amwalStatus, array $amwalData): bool
    {
        if ($amwalStatus && in_array($amwalStatus, self::REFUND_STATUSES, true)) {
            return true;
        }

        // Also check if refunded_amount is present and > 0
        $refundedAmount = (float)($amwalData['refunded_amount'] ?? 0);
        return $refundedAmount > 0;
    }

    /**
     * Check whether the order should be skipped for refund processing.
     *
     * @param Order $order
     * @param array $amwalData
     * @return bool
     */
    private function shouldSkipProcessing(Order $order, array $amwalData): bool
    {
        $incrementId = $order->getIncrementId();

        // Cannot refund a closed order (already fully refunded)
        if ($order->getState() === Order::STATE_CLOSED) {
            $this->logger->info("Order #{$incrementId} is already closed (fully refunded). Skipping.");
            return true;
        }

        // Cannot refund an order that is not in processing or complete state
        if (!in_array($order->getState(), [Order::STATE_PROCESSING, Order::STATE_COMPLETE])) {
            $this->logger->info("Order #{$incrementId} is in state {$order->getState()} and cannot be refunded. Skipping.");
            return true;
        }

        // Check if the refund amount has already been fully processed
        $requestedRefundAmount = $this->roundValue((float)($amwalData['refunded_amount'] ?? 0));
        $alreadyRefunded = $this->getActualRefundedAmount($order);

        if ($requestedRefundAmount <= 0) {
            $this->logger->info("Order #{$incrementId} - No refund amount in webhook data. Skipping.");
            return true;
        }

        if ($alreadyRefunded >= $requestedRefundAmount) {
            $this->logger->info(
                "Order #{$incrementId} - Already refunded {$alreadyRefunded}, " .
                "webhook refunded_amount is {$requestedRefundAmount}. Skipping duplicate refund."
            );
            return true;
        }

        $this->logger->info(
            "Order #{$incrementId} - Proceeding with refund. " .
            "Already refunded: {$alreadyRefunded}, Webhook refunded_amount: {$requestedRefundAmount}"
        );
        return false;
    }

    /**
     * Get the actual refunded amount by summing existing credit memos.
     * This is more reliable than getTotalRefunded() which may not be updated yet.
     *
     * @param Order $order
     * @return float
     */
    private function getActualRefundedAmount(Order $order): float
    {
        $total = 0.0;
        foreach ($order->getCreditmemosCollection() as $creditmemo) {
            $total += (float)$creditmemo->getGrandTotal();
        }
        return $this->roundValue($total);
    }

    /**
     * Process the refund for the order.
     *
     * @param Order $order
     * @param array $amwalData
     * @return void
     * @throws LocalizedException
     */
    private function processRefund(Order $order, array $amwalData): void
    {
        $transactionId = $amwalData['id'] ?? null;
        $refundedAmount = $this->roundValue((float)($amwalData['refunded_amount'] ?? 0));
        $alreadyRefunded = $this->getActualRefundedAmount($order);
        $amountToRefund = $this->roundValue($refundedAmount - $alreadyRefunded);

        if ($amountToRefund <= 0) {
            $this->logger->info("Order #{$order->getIncrementId()} - Computed refund amount is zero or negative. Skipping.");
            return;
        }

        $this->logger->info(
            "[Webhook] Processing refund for order #{$order->getIncrementId()} " .
            "- Amount to refund: {$amountToRefund} (Amwal refunded_amount: {$refundedAmount}, Already refunded: {$alreadyRefunded})"
        );

        // Create credit memo (this handles payment transaction recording internally)
        $this->createCreditMemo($order, $amountToRefund, $transactionId);

        // Reload order after credit memo to get updated totals
        $order = $this->orderRepository->get($order->getId());

        // Update order status
        $this->updateOrderStatus($order, $refundedAmount, $amwalData);

        // Save the order
        $this->orderRepository->save($order);

        $this->logger->info("Refund processing completed for order #{$order->getIncrementId()}");
    }

    /**
     * Create credit memo for the refund.
     *
     * @param Order $order
     * @param float $amountToRefund
     * @param string|null $transactionId
     * @return void
     */
    private function createCreditMemo(Order $order, float $amountToRefund, ?string $transactionId): void
    {
        if (!$order->canCreditmemo()) {
            $this->logger->info("Order #{$order->getIncrementId()} cannot have a credit memo created. Skipping credit memo.");
            $order->addCommentToStatusHistory(
                __('[Webhook] Refund of %1 received from Amwal but credit memo could not be created automatically.', $amountToRefund),
                false,
                true
            );
            $this->orderRepository->save($order);
            return;
        }

        $this->logger->info("[Webhook] Creating credit memo for order #{$order->getIncrementId()} - Amount: {$amountToRefund}");

        try {
            $grandTotal = $this->roundValue((float)$order->getGrandTotal());
            $alreadyRefunded = $this->getActualRefundedAmount($order);
            $remainingRefundable = $this->roundValue($grandTotal - $alreadyRefunded);

            // Clamp the refund amount to what's actually refundable
            if ($amountToRefund > $remainingRefundable) {
                $this->logger->info(
                    "Order #{$order->getIncrementId()} - Clamping refund from {$amountToRefund} to {$remainingRefundable} (max refundable)"
                );
                $amountToRefund = $remainingRefundable;
            }

            if ($amountToRefund <= 0) {
                $this->logger->info("Order #{$order->getIncrementId()} - Nothing left to refund after clamping. Skipping.");
                return;
            }

            $isFullRefund = ($amountToRefund >= $remainingRefundable);

            $creditmemo = $this->creditmemoFactory->createByOrder($order);

            if (!$isFullRefund) {
                $this->adjustCreditMemoForPartialRefund($creditmemo, $order, $amountToRefund);
            }

            // Mark as offline so Magento doesn't call the payment gateway again
            // (the refund was already processed on Amwal's side)
            $creditmemo->setOfflineRequested(true);
            $creditmemo->addComment(
                __('[Webhook] Refund processed via Amwal webhook. Transaction ID: %1', $transactionId ?? 'N/A'),
                false,
                true
            );

            $this->creditmemoManagement->refund($creditmemo, true);

            $this->sendCreditmemoEmail($creditmemo);

            $this->logger->info(
                "Credit memo #{$creditmemo->getIncrementId()} created successfully " .
                "for order #{$order->getIncrementId()} - Amount: {$amountToRefund}"
            );
        } catch (\Exception $e) {
            $this->logger->error("Failed to create credit memo for order #{$order->getIncrementId()}: " . $e->getMessage());
            $order->addCommentToStatusHistory(
                __('[Webhook] Failed to create credit memo for refund amount %1: %2', $amountToRefund, $e->getMessage()),
                false,
                true
            );
            $this->orderRepository->save($order);
        }
    }

    /**
     * Adjust credit memo amounts for partial refund.
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @param Order $order
     * @param float $amountToRefund
     * @return void
     */
    private function adjustCreditMemoForPartialRefund($creditmemo, Order $order, float $amountToRefund): void
    {
        // Zero out all item qtys — we use adjustment to control the refund amount
        foreach ($creditmemo->getAllItems() as $item) {
            $item->setQty(0);
        }

        // Set shipping to 0 for partial refund (adjust if you want to include shipping)
        $creditmemo->setShippingAmount(0);
        $creditmemo->setBaseShippingAmount(0);

        // Use positive adjustment to set exact refund amount
        $creditmemo->setAdjustmentPositive($amountToRefund);
        $creditmemo->setAdjustmentNegative(0);
        $creditmemo->setSubtotal(0);
        $creditmemo->setBaseSubtotal(0);
        $creditmemo->setGrandTotal($amountToRefund);
        $creditmemo->setBaseGrandTotal($amountToRefund);
    }

    /**
     * Update order status after refund.
     *
     * @param Order $order
     * @param float $totalRefundedAmount
     * @param array $amwalData
     * @return void
     */
    private function updateOrderStatus(Order $order, float $totalRefundedAmount, array $amwalData): void
    {
        $grandTotal = $this->roundValue((float)$order->getGrandTotal());
        $amwalStatus = $amwalData['status'] ?? null;

        $isFullRefund = ($totalRefundedAmount >= $grandTotal) || ($amwalStatus === 'refunded');

        if ($isFullRefund) {
            $order->setState(Order::STATE_CLOSED);
            $order->setStatus(Order::STATE_CLOSED);
            $order->addCommentToStatusHistory(
                __('[Webhook] Order fully refunded via Amwal. Order closed.'),
                Order::STATE_CLOSED,
                true
            );
            $this->logger->info("Order #{$order->getIncrementId()} fully refunded - status set to closed");
        } else {
            $actualRefunded = $this->getActualRefundedAmount($order);
            $order->addCommentToStatusHistory(
                __('[Webhook] Partial refund processed via Amwal. Total refunded: %1 of %2',
                    $actualRefunded,
                    $grandTotal
                ),
                false,
                true
            );
            $this->logger->info(
                "Order #{$order->getIncrementId()} partially refunded - " .
                "Refunded: {$actualRefunded} of {$grandTotal}"
            );
        }
    }

    /**
     * Send credit memo email, suppressing exceptions.
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return void
     */
    private function sendCreditmemoEmail($creditmemo): void
    {
        try {
            $this->creditmemoSender->send($creditmemo);
        } catch (\Exception $e) {
            $this->logger->warning("Could not send credit memo email: " . $e->getMessage());
        }
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

