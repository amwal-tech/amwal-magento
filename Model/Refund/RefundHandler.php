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


    public function initiateCreditMemo(OrderInterface $order, array $refundItems, float $refundAmount, float $refundShippingAmount)
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
        $this->creditmemoRepository->save($creditMemo);

        $refundAmountFormatted = $order->getBaseCurrency()->formatTxt($refundAmount);

        $creditMemo->addComment('We refunded ' . $refundAmountFormatted . ' online by Amwal Payments.');
        $creditMemo->setState(Creditmemo::STATE_REFUNDED);
        $creditMemo->setBaseGrandTotal($refundAmount);
        $creditMemo->setGrandTotal($refundAmount);

        foreach ($order->getAllItems() as $orderItem) {
            if (isset($refundItems['qtys'][$orderItem->getId()]) && $refundItems['qtys'][$orderItem->getId()] > 0) {
                $orderItem->setQtyRefunded($orderItem->getQtyRefunded() + $refundItems['qtys'][$orderItem->getId()]);
                $orderItem->setAmountRefunded($orderItem->getAmountRefunded() + $refundAmount);
            }
        }
        $order->setTotalRefunded($order->getTotalRefunded() + $refundAmount);
        $order->addStatusHistoryComment(__('We refunded %1 online by Amwal Payments.', $refundAmountFormatted));

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
