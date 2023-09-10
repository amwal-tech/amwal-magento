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
    protected $creditmemoItemFactory;

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


    public function initiateCreditMemo(OrderInterface $order, array $refundItems, float $refundAmount, float $shippingAmount, float $adjustmentPositive, float $adjustmentNegative)
    {

        $refundItems = $this->getItemsToRefund($order, $refundItems);
        $invoice = $order->getInvoiceCollection()->getFirstItem();
        $creditMemo = $this->creditmemoFactory->createByInvoice(
            $invoice,
            $refundItems
        );

        foreach ($creditMemo->getAllItems() as $creditmemoItem) {
            $creditmemoItem->setQty($creditmemoItem->getQty());
        }

        $refundAmountFormatted = $order->getBaseCurrency()->formatTxt($refundAmount);
        $creditMemo->addComment(__('We refunded %1 online by Amwal Payments.', $refundAmountFormatted));
        $creditMemo->setState(Creditmemo::STATE_REFUNDED);
        $creditMemo->setGrandTotal($refundAmount);
        $creditMemo->setShippingAmount($shippingAmount);
        $creditMemo->setAdjustmentPositive($adjustmentPositive);
        $creditMemo->setAdjustmentNegative($adjustmentNegative);

        foreach ($order->getAllItems() as $orderItem) {
            $itemId = $orderItem->getId();
            if (isset($refundItems['qtys'][$itemId]) && $refundItems['qtys'][$itemId] > 0) {
                $refundQty = $refundItems['qtys'][$itemId];
                $itemPrice = $orderItem->getPrice() * $refundQty;
                if ($orderItem->getDiscountAmount() > 0) {
                    $itemPrice -= $orderItem->getDiscountAmount() * ($refundQty / $orderItem->getQtyOrdered());
                    $itemPrice += $orderItem->getTaxAmount() * ($refundQty / $orderItem->getQtyOrdered());
                }

                $orderItem->setAmountRefunded($orderItem->getAmountRefunded() + $itemPrice);
                $orderItem->setQtyRefunded($orderItem->getQtyRefunded() + $refundQty);
            }
        }

        $order->setTotalRefunded($order->getTotalRefunded() + $refundAmount);
        $order->addStatusHistoryComment(__('We refunded %1 online by Amwal Payments.', $refundAmountFormatted));

        $order->save();
        $this->creditmemoRepository->save($creditMemo);

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
        $creditmemoItem = $this->creditmemoItemFactory->create();
        $creditmemoItem->setOrderItemId($orderItem->getId());
        $creditmemoItem->setQty($refundQty);
        $creditmemoItem->setPrice($orderItem->getPrice());
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
