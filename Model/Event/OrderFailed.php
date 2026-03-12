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
use Magento\Framework\App\ResourceConnection;

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
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderManagementInterface $orderManagement
     * @param LoggerInterface $logger
     * @param StockManagementInterface $stockManagement
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param GetSalableQuantityDataBySku $getSalableQuantityDataBySku
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderManagementInterface $orderManagement,
        LoggerInterface $logger,
        StockManagementInterface $stockManagement,
        SourceItemsSaveInterface $sourceItemsSave,
        SourceItemInterfaceFactory $sourceItemFactory,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        ResourceConnection $resourceConnection
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
        $this->logger = $logger;
        $this->stockManagement = $stockManagement;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
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
        try {
            $this->logger->info('Processing order.failed for order #' . $order->getIncrementId());

            // Reload order from DB to get the freshest state
            $order = $this->orderRepository->get($order->getId());

            if ($this->shouldSkipProcessing($order)) {
                return;
            }

            $reason = $data['data']['failure_reason'] ?? 'Payment failed';

            $this->updatePaymentInfo($order, $data);
            $this->restoreOrderStock($order);

            if ($order->canCancel()) {
                $this->cancelOrder($order, $reason);
            } else {
                $this->handleUncancellableOrder($order, $reason);
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
     * Check whether the order should be skipped for processing.
     *
     * @param Order $order
     * @return bool
     */
    private function shouldSkipProcessing(Order $order): bool
    {
        $incrementId = $order->getIncrementId();

        if (in_array($order->getState(), [Order::STATE_CANCELED, Order::STATE_CLOSED])) {
            $this->logger->info("Order #{$incrementId} is already in state {$order->getState()}. Skipping.");
            return true;
        }

        if (in_array($order->getState(), [Order::STATE_PROCESSING, Order::STATE_COMPLETE])) {
            $this->logger->info("Order #{$incrementId} is already in state {$order->getState()} (payment succeeded). Skipping failure webhook.");
            return true;
        }

        if ($order->getData('amwal_webhook_processed')) {
            $this->logger->info("Order #{$incrementId} already has amwal_webhook_processed flag. Skipping.");
            return true;
        }

        if ($order->getPayment()->getAdditionalInformation('amwal_failure_reason')) {
            $this->logger->info("Order #{$incrementId} already has failure reason in payment info. Skipping.");
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
     * Update payment information with failure details.
     *
     * @param Order $order
     * @param array $data
     * @return void
     */
    private function updatePaymentInfo(Order $order, array $data): void
    {
        $reason = $data['data']['failure_reason'] ?? 'Payment failed';
        $transactionId = $data['data']['id'] ?? null;

        $payment = $order->getPayment();
        if ($transactionId) {
            $payment->setTransactionId($transactionId);
            $payment->setLastTransId($transactionId);
        }

        $payment->setAdditionalInformation('amwal_failure_reason', $reason);
        $order->setData('amwal_webhook_processed', true);

        $this->orderRepository->save($order);
    }

    /**
     * Cancel the order and add status history comments.
     *
     * @param Order $order
     * @param string $reason
     * @return void
     */
    private function cancelOrder(Order $order, string $reason): void
    {
        $order->setState(Order::STATE_CANCELED)
            ->setStatus(Order::STATE_CANCELED);

        $comment = $order->addCommentToStatusHistory(
            __('[Webhook] Payment failed with Amwal. Reason: %1. Stock quantities restored.', $reason),
            Order::STATE_CANCELED,
            true
        );

        $this->logComment($comment, $order);
        $this->orderRepository->save($order);

        $this->orderManagement->cancel($order->getId());

        $updatedOrder = $this->orderRepository->get($order->getId());
        if ($updatedOrder->getStatus() !== Order::STATE_CANCELED) {
            $updatedOrder->setStatus(Order::STATE_CANCELED);
        }

        $updatedOrder->addCommentToStatusHistory(
            __('[Webhook] Order was cancelled due to payment failure with Amwal. Stock restored.'),
            Order::STATE_CANCELED,
            true
        );

        $this->orderRepository->save($updatedOrder);
        $this->logger->info("Order #{$order->getIncrementId()} cancelled due to payment failure and stock restored");
    }

    /**
     * Handle orders that cannot be cancelled.
     *
     * @param Order $order
     * @param string $reason
     * @return void
     */
    private function handleUncancellableOrder(Order $order, string $reason): void
    {
        $paymentFailedStatus = $order->getState() . '_payment_failed';
        $order->setStatus($paymentFailedStatus);

        $comment = $order->addCommentToStatusHistory(
            __('[Webhook] Payment failed with Amwal but order could not be cancelled. Reason: %1. Stock restored.', $reason),
            $paymentFailedStatus,
            true
        );

        $this->logComment($comment, $order);
        $this->orderRepository->save($order);
        $this->logger->info("Order #{$order->getIncrementId()} could not be cancelled. Added comment only. Status set to: {$paymentFailedStatus}. Stock restored.");
    }

    /**
     * Log comment creation result.
     *
     * @param mixed $comment
     * @param Order $order
     * @return void
     */
    private function logComment($comment, Order $order): void
    {
        if ($comment && $comment->getId()) {
            $this->logger->info("Added comment ID: " . $comment->getId() . " to order #{$order->getIncrementId()}");
        } else {
            $this->logger->info("Comment was not created for order #{$order->getIncrementId()}");
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
