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
     * @param float $totalDiscount
     * @param float $totalTax
     * @return bool
     * @throws \Exception
     */
    public function initiateCreditMemo(
        OrderInterface $order,
        array          $refundItems,
        float          $refundAmount,
        float          $shippingAmount,
        float          $adjustmentPositive,
        float          $adjustmentNegative,
        float          $totalDiscount,
        float          $totalTax
    ): bool
    {
        $refundItems = $this->getItemsToRefund($order, $refundItems);
        $creditMemo = $this->creditmemoFactory->createByOrder($order, $refundItems);

        foreach ($order->getAllItems() as $orderItem) {
            $itemId = $orderItem->getId();
            if (isset($refundItems[$itemId]) && $refundItems[$itemId]['qty'] > 0) {
                $refundQty = $refundItems[$itemId]['qty'];
                $itemPrice = $refundItems[$itemId]['price'];
                $orderItem->setTaxRefunded($orderItem->getTaxRefunded() + $refundItems[$itemId]['tax']);
                $orderItem->setDiscountRefunded($orderItem->getDiscountRefunded() + $refundItems[$itemId]['discount_amount']);
                $orderItem->setQtyRefunded($orderItem->getQtyRefunded() + $refundQty);
                $orderItem->setAmountRefunded($orderItem->getAmountRefunded() + $itemPrice);
                $orderItem->setBaseAmountRefunded($orderItem->getBaseAmountRefunded() + $itemPrice);
            }
        }

        // Set credit memo properties
        $creditMemo->setState(Creditmemo::STATE_REFUNDED);
        $creditMemo->setShippingAmount($shippingAmount);
        $creditMemo->setBaseGrandTotal($refundAmount);
        $creditMemo->setGrandTotal($refundAmount);
        $creditMemo->setAdjustmentPositive($adjustmentPositive);
        $creditMemo->setAdjustmentNegative($adjustmentNegative);
        $creditMemo->setDiscountAmount($totalDiscount);

        // Format refund amount for display
        $refundAmountFormatted = $order->getBaseCurrency()->formatTxt($refundAmount);
        $creditMemo->addComment(__('We refunded %1 online by Amwal Payments.', $refundAmountFormatted));
        if($totalTax > 0) {
            $creditMemo->setTaxAmount($order->getTaxAmount() + $totalTax);
            $creditMemo->setBaseTaxAmount( $order->getBaseTaxAmount() + $totalTax);
        }
        if ($totalDiscount > 0) {
            $order->setDiscountRefunded($order->getDiscountRefunded() + $totalDiscount);
        }
        if ($shippingAmount > 0) {
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
        foreach ($refundItems as $refundItemData) {
            $itemId = $refundItemData['item_id'];
            $refundQty = $refundItemData['qty'];
            $tax = $refundItemData['tax'];
            $discountAmount = $refundItemData['discount_amount'];
            $price = $refundItemData['price'];

            $orderItem = $this->getOrderItemById($order, $itemId);
            if ($orderItem) {
                $creditmemoItem = $this->createCreditMemoItem($orderItem, $refundQty, $tax, $discountAmount, $price);
                $refundableItems[$itemId] = $creditmemoItem;
            }
        }
        return $refundableItems;
    }

    protected function createCreditMemoItem($orderItem, $refundQty, $tax, $discountAmount, $price)
    {
        $creditmemoItem = $this->creditmemoItemFactory->create();
        $creditmemoItem->setOrderItemId($orderItem->getId());
        $creditmemoItem->setQty($refundQty);
        $creditmemoItem->setTaxAmount($tax);
        $creditmemoItem->setDiscountAmount($discountAmount);
        $creditmemoItem->setPrice($price);
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
