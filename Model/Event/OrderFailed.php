<?php
namespace Amwal\Payments\Model\Event;

use Amwal\Payments\Model\Event\HandlerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Framework\Exception\NoSuchEntityException;

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
     * @var StockManagementInterface
     */
    private $stockManagement;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @var SourceItemInterfaceFactory
     */
    private $sourceItemFactory;

    /**
     * @var GetSalableQuantityDataBySku
     */
    private $getSalableQuantityDataBySku;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderManagementInterface $orderManagement
     * @param LoggerInterface $logger
     * @param StockManagementInterface $stockManagement
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param GetSalableQuantityDataBySku $getSalableQuantityDataBySku
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderManagementInterface $orderManagement,
        LoggerInterface $logger,
        StockManagementInterface $stockManagement,
        SourceItemsSaveInterface $sourceItemsSave,
        SourceItemInterfaceFactory $sourceItemFactory,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
        $this->logger = $logger;
        $this->stockManagement = $stockManagement;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
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
            // Set custom field for webhook processing
            $order->setData('amwal_webhook_processed', true);

            // Save payment changes
            $this->orderRepository->save($order);

            // Restore stock before canceling
            $this->restoreOrderStock($order);

            // Cancel the order
            if ($order->canCancel()) {
                // Explicitly set both state and status to CANCELED
                $order->setState(Order::STATE_CANCELED)
                    ->setStatus(Order::STATE_CANCELED);

                // Add comment with payment failure reason
                $comment = $order->addCommentToStatusHistory(
                    __('[Webhook] Payment failed with Amwal. Reason: %1. Stock quantities restored.', $reason),
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
                    __('[Webhook] Order was cancelled due to payment failure with Amwal. Stock restored.'),
                    Order::STATE_CANCELED,
                    true
                );

                // Save the order again to ensure the confirmation comment is saved
                $this->orderRepository->save($updatedOrder);

                $this->logger->info("Order #{$order->getIncrementId()} cancelled due to payment failure and stock restored");
            } else {
                // If order cannot be cancelled, just add a comment
                // Set a proper status even if we can't cancel
                $currentState = $order->getState();
                $paymentFailedStatus = $currentState . '_payment_failed';

                // Set the status to indicate payment failure
                $order->setStatus($paymentFailedStatus);

                $comment = $order->addCommentToStatusHistory(
                    __('[Webhook] Payment failed with Amwal but order could not be cancelled. Reason: %1. Stock restored.', $reason),
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
                $this->logger->info("Order #{$order->getIncrementId()} could not be cancelled. Added comment only. Status set to: {$paymentFailedStatus}. Stock restored.");
            }
        } catch (\Exception $e) {
            $this->logger->critical(
                "Error processing order.failed webhook for order #{$order->getIncrementId()}: " . $e->getMessage(),
                ['exception' => $e]
            );
            throw new LocalizedException(__('Error processing payment failure: %1', $e->getMessage()));
        }
    }

    /**
     * Restore stock for order items
     *
     * @param Order $order
     * @return void
     */
    private function restoreOrderStock(Order $order)
    {
        try {
            $this->logger->info("Starting stock restoration for order #{$order->getIncrementId()}");

            $items = $order->getAllVisibleItems();
            $sourceItemsToSave = [];

            foreach ($items as $item) {
                if ($item->getProductType() === 'simple') {
                    $sku = $item->getSku();
                    $qtyOrdered = (float)$item->getQtyOrdered();

                    $this->logger->info("Restoring stock for SKU: {$sku}, Quantity: {$qtyOrdered}");

                    try {
                        // Method 1: Using legacy stock management (works with older Magento versions)
                        $this->stockManagement->backItemQty($sku, $qtyOrdered);

                        $this->logger->info("Successfully restored {$qtyOrdered} units for SKU: {$sku} using legacy method");

                    } catch (\Exception $legacyException) {
                        $this->logger->warning("Legacy stock restoration failed for SKU: {$sku}. Trying MSI method. Error: " . $legacyException->getMessage());

                        try {
                            // Method 2: Using MSI (Multi-Source Inventory) for newer Magento versions
                            $salableQuantityData = $this->getSalableQuantityDataBySku->execute($sku);

                            foreach ($salableQuantityData as $sourceData) {
                                $sourceCode = $sourceData['source_code'];

                                $sourceItem = $this->sourceItemFactory->create();
                                $sourceItem->setSourceCode($sourceCode);
                                $sourceItem->setSku($sku);
                                $sourceItem->setQuantity($sourceData['qty'] + $qtyOrdered);
                                $sourceItem->setStatus(SourceItemInterface::STATUS_IN_STOCK);

                                $sourceItemsToSave[] = $sourceItem;

                                $this->logger->info("Prepared to restore {$qtyOrdered} units for SKU: {$sku} in source: {$sourceCode}");
                                break; // Use the first available source
                            }

                        } catch (\Exception $msiException) {
                            $this->logger->error("MSI stock restoration also failed for SKU: {$sku}. Error: " . $msiException->getMessage());
                        }
                    }
                }
            }

            // Save MSI source items if any were prepared
            if (!empty($sourceItemsToSave)) {
                try {
                    $this->sourceItemsSave->execute($sourceItemsToSave);
                    $this->logger->info("Successfully saved MSI stock updates for order #{$order->getIncrementId()}");
                } catch (\Exception $saveException) {
                    $this->logger->error("Failed to save MSI stock updates for order #{$order->getIncrementId()}: " . $saveException->getMessage());
                }
            }

            $this->logger->info("Completed stock restoration for order #{$order->getIncrementId()}");

        } catch (\Exception $e) {
            $this->logger->error("Error during stock restoration for order #{$order->getIncrementId()}: " . $e->getMessage());
            // Don't throw exception here to avoid blocking the order cancellation
        }
    }
}
