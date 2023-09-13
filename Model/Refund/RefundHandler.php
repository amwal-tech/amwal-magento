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
    protected CreditmemoRepositoryInterface $creditmemoRepository;
    protected CreditmemoFactory $creditmemoFactory;
    protected ItemFactory $creditmemoItemFactory;

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

    /**
     * @param OrderInterface $order
     * @param array $refundItems
     * @param float $refundAmount
     * @param float $shippingAmount
     * @param float $adjustmentPositive
     * @param float $adjustmentNegative
     * @return bool
     * @throws \Exception
     */
    public function initiateCreditMemo(
        OrderInterface $order,
        array $refundItems,
        float $refundAmount,
        float $shippingAmount,
        float $adjustmentPositive,
        float $adjustmentNegative
    ): bool
    {
        $refundItems = $this->getItemsToRefund($order, $refundItems);
        $invoice = $order->getInvoiceCollection()->getFirstItem();
        $creditMemo = $this->creditmemoFactory->createByInvoice(
            $invoice,
            $refundItems
        );
        $totalTax = 0;
        $totalDiscount = 0;

        foreach ($order->getAllItems() as $orderItem) {
            $itemId = $orderItem->getId();
            if (isset($refundItems['qtys'][$itemId]) && $refundItems['qtys'][$itemId] > 0) {
                $refundQty = $refundItems['qtys'][$itemId];
                if ($orderItem->getDiscountAmount() > 0) {
                    $itemPrice = $orderItem->getPrice() * $refundQty;
                    $currentQty = $orderItem->getQtyOrdered() - $orderItem->getQtyRefunded();
                    $itemDiscount = (($orderItem->getDiscountAmount() / $currentQty) * $refundQty);
                    $totalDiscount += ($orderItem->getDiscountAmount() / $orderItem->getQtyOrdered() * $refundQty);
                    $itemPrice -= $itemDiscount;
                    $orderItem->setDiscountRefunded($orderItem->getDiscountRefunded() + $totalDiscount);
                } else {
                    $itemPrice = $orderItem->getPrice() * $refundQty;
                }
                $totalTax += ($orderItem->getTaxAmount() / $orderItem->getQtyOrdered() * $refundQty);

                $orderItem->setQtyRefunded($orderItem->getQtyRefunded() + $refundQty);
                $orderItem->setTaxRefunded($orderItem->getTaxRefunded() + $totalTax);
                $orderItem->setAmountRefunded($orderItem->getAmountRefunded() + $itemPrice);
            }
        }
        // Set credit memo properties
        $creditMemo->setState(Creditmemo::STATE_REFUNDED);
        $creditMemo->setAdjustmentPositive($adjustmentPositive);
        $creditMemo->setAdjustmentNegative($adjustmentNegative);
        $creditMemo->setShippingAmount($shippingAmount);
        $creditMemo->setShippingInclTax($shippingAmount);
        $creditMemo->setGrandTotal($refundAmount);

        // Format refund amount for display
        $refundAmountFormatted = $order->getBaseCurrency()->formatTxt($refundAmount);
        $creditMemo->addComment(__('We refunded %1 online by Amwal Payments.', $refundAmountFormatted));

        // Update order-level tax and discount refunded, as well as shipping amount and total refunded
        $order->setTaxRefunded($order->getTaxRefunded() + $totalTax);
        if ($totalDiscount > 0){
            $order->setDiscountRefunded($order->getDiscountRefunded() + $totalDiscount);
        }
        if($shippingAmount > 0){
            $order->setBaseShippingRefunded(abs($order->getBaseShippingRefunded() - $shippingAmount));
            $order->setShippingRefunded(abs($order->getShippingRefunded() - $shippingAmount));

        }
        $order->setTotalRefunded($order->getTotalRefunded() + $refundAmount);
        $order->setBaseTotalRefunded($order->getBaseTotalRefunded() + $refundAmount);
        $order->addStatusHistoryComment(__('We refunded %1 online by Amwal Payments.', $refundAmountFormatted));

        // Save order and credit memo
        $order->save();
        $this->creditmemoRepository->save($creditMemo);

        return true;
    }


    protected function getItemsToRefund($order, array $refundItems): array
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
