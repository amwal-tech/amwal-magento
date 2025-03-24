<?php
namespace Amwal\Payments\Model\Event;

use Amwal\Payments\Model\Event\HandlerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * Handles order.failed webhook event
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderFailed implements HandlerInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderManagementInterface $orderManagement
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderManagementInterface $orderManagement,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
        $this->logger = $logger;
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
        try {
            $this->logger->info('Processing order.failed for order #' . $order->getIncrementId());

            // Don't process already cancelled/closed orders
            if ($order->getState() === Order::STATE_CANCELED || $order->getState() === Order::STATE_CLOSED) {
                $this->logger->info("Order #{$order->getIncrementId()} is already in state {$order->getState()}. Skipping.");
                return;
            }

            // Get failure reason
            $reason = $data['data']['failure_reason'] ?? 'Payment failed';
            $transactionId = $data['data']['id'] ?? null;

            // Update payment information
            $payment = $order->getPayment();
            if ($transactionId ?? false) {
                $payment->setTransactionId($transactionId);
                $payment->setLastTransId($transactionId);
            }

            // Add failure details to payment
            $payment->setAdditionalInformation('amwal_failure_reason', $reason);

            // Save payment changes
            $this->orderRepository->save($order);

            // Cancel the order
            if ($order->canCancel()) {
                // Explicitly set both state and status to CANCELED
                $order->setState(Order::STATE_CANCELED)
                    ->setStatus(Order::STATE_CANCELED);

                // Add comment with payment failure reason
                $comment = $order->addCommentToStatusHistory(
                    __('[Webhook] Payment failed with Amwal. Reason: %1', $reason),
                    Order::STATE_CANCELED,
                    true
                );

                // Log comment ID for debugging
                if ($comment && $comment->getId()) {
                    $this->logger->info("Added comment ID: " . $comment->getId() . " to order #{$order->getIncrementId()}");
                } else {
                    $this->logger->info("Comment was not created for order #{$order->getIncrementId()}");
                }

                // Save order with comment before cancellation
                $this->orderRepository->save($order);

                // Cancel the order
                $this->orderManagement->cancel($order->getId());

                // Reload the order to ensure we have the latest state
                $updatedOrder = $this->orderRepository->get($order->getId());

                // Ensure status is still set after cancel operation
                if ($updatedOrder->getStatus() !== Order::STATE_CANCELED) {
                    $updatedOrder->setStatus(Order::STATE_CANCELED);
                }

                // Add another comment confirming cancellation
                $updatedOrder->addCommentToStatusHistory(
                    __('[Webhook] Order was cancelled due to payment failure with Amwal.'),
                    Order::STATE_CANCELED,
                    true
                );

                // Save the order again to ensure the confirmation comment is saved
                $this->orderRepository->save($updatedOrder);

                $this->logger->info("Order #{$order->getIncrementId()} cancelled due to payment failure");
            } else {
                // If order cannot be cancelled, just add a comment
                // Set a proper status even if we can't cancel
                $currentState = $order->getState();
                $paymentFailedStatus = $currentState . '_payment_failed';

                // Set the status to indicate payment failure
                $order->setStatus($paymentFailedStatus);

                $comment = $order->addCommentToStatusHistory(
                    __('[Webhook] Payment failed with Amwal but order could not be cancelled. Reason: %1', $reason),
                    $paymentFailedStatus,
                    true
                );

                // Log comment ID for debugging
                if ($comment && $comment->getId()) {
                    $this->logger->info("Added comment ID: " . $comment->getId() . " to order #{$order->getIncrementId()}");
                } else {
                    $this->logger->info("Comment was not created for order #{$order->getIncrementId()}");
                }

                $this->orderRepository->save($order);
                $this->logger->info("Order #{$order->getIncrementId()} could not be cancelled. Added comment only. Status set to: {$paymentFailedStatus}");
            }
        } catch (\Exception $e) {
            $this->logger->critical(
                "Error processing order.failed webhook for order #{$order->getIncrementId()}: " . $e->getMessage(),
                ['exception' => $e]
            );
            throw new LocalizedException(__('Error processing payment failure: %1', $e->getMessage()));
        }
    }
}
