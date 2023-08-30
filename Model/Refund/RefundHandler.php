<?php

declare(strict_types=1);

namespace Amwal\Payments\Model\Refund;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Creditmemo\ItemFactory;
use Magento\Sales\Model\Order\Creditmemo;

class RefundHandler
{
    protected $creditmemoRepository;
    protected $creditmemoFactory;

    public function __construct(
        CreditmemoRepositoryInterface $creditmemoRepository,
        CreditmemoFactory             $creditmemoFactory,
        ItemFactory                   $creditmemoItemFactory
    )
    {
        $this->creditmemoRepository = $creditmemoRepository;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoItemFactory = $creditmemoItemFactory;
    }

    public function initiateCreditMemo(OrderInterface $order, array $refundItems, float $refundAmount, float $refundShippingAmount)
    {

        $refundItems = $this->getItemsToRefund($order, $refundItems);

        // Create a credit memo with selected items and refund amount
        $creditMemo = $this->creditmemoFactory->createByInvoice(
            $order->getInvoiceCollection()->getFirstItem(),
            $refundItems
        );

        // Manually refund selected items
        foreach ($creditMemo->getAllItems() as $creditmemoItem) {
            $creditmemoItem->setQty($creditmemoItem->getQty());
        }

        // Save the credit memo
        $this->creditmemoRepository->save($creditMemo);

        $refundAmountFormatted = $order->getBaseCurrency()->formatTxt($refundAmount);

        // set refund comment
        $creditMemo->addComment('We refunded ' . $refundAmountFormatted . ' online by Amwal Payments.');
        $order->addStatusHistoryComment('We refunded ' . $refundAmountFormatted . ' online by Amwal Payments.');

        // set memo state
        $creditMemo->setState(Creditmemo::STATE_REFUNDED);

        // update the order qty
        $order->setTotalRefunded($order->getTotalRefunded() + $refundAmount);

        // update the order Items Ordered qty
        foreach ($order->getAllItems() as $orderItem) {
            if (isset($refundItems['qtys'][$orderItem->getId()]) && $refundItems['qtys'][$orderItem->getId()] > 0) {
                $orderItem->setQtyRefunded($orderItem->getQtyRefunded() + $refundItems['qtys'][$orderItem->getId()]);
            }
        }

        // Save credit memo
        $creditMemo->save();
        $order->save();

        return true;
    }

    protected function getItemsToRefund($order, array $refundItems)
    {
        $refundableItems = [];
        $qtys = [];
        foreach ($refundItems as $refundItemData) {
            $itemId = $refundItemData['item_id'];
            $refundQty = $refundItemData['qty'];

            $orderItem = $this->getOrderItemById($order, $itemId);

            if ($orderItem) {
                // Create a credit memo item for the selected order item
                $creditmemoItem = $this->createCreditMemoItem($orderItem, $refundQty);
                $refundableItems[] = $creditmemoItem;
            }
        }

        foreach ($refundableItems as $item) {
            $qtys[$item->getOrderItemId()] = $item->getQty();
        }
        $refundableItems['qtys'] = $qtys;
        $refundableItems['shipping_amount'] = $order->getShippingAmount();

        return $refundableItems;
    }

    protected function createCreditMemoItem($orderItem, $refundQty)
    {
        // Create a credit memo item for the selected order item
        $creditmemoItem = $this->creditmemoItemFactory->create();
        $creditmemoItem->setOrderItemId($orderItem->getId());
        $creditmemoItem->setQty($refundQty);

        return $creditmemoItem;
    }

    protected function getOrderItemById($order, $itemId)
    {
        foreach ($order->getAllVisibleItems() as $orderItem) {
            if ($orderItem->getId() == $itemId) {
                return $orderItem;
            }
        }
        return null;
    }
}
