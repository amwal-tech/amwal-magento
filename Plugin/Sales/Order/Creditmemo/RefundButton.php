<?php
declare(strict_types=1);

namespace Amwal\Payments\Plugin\Sales\Order\Creditmemo;

use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items;

class RefundButton
{
    protected $context;
    protected $refundButtonBlock = null;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    public function afterGetChildHtml(Items $subject, $result)
    {
        if ($result) {
            $order = $subject->getCreditmemo()->getOrder();
            $orderId = $order->getId();

            $this->refundButtonBlock = $this->context->getLayout()->createBlock(
                \Magento\Backend\Block\Template::class,
            );
            $this->refundButtonBlock->setData('orderId', $orderId);
            $this->refundButtonBlock->setTemplate('Amwal_Payments::order/creditmemo/refund-button.phtml');
        }
        return $result;
    }

    public function afterToHtml(Items $subject, $result)
    {
        if ($this->refundButtonBlock) {
            $actionsDivStart = strpos($result, '<div class="actions">');

            $actionsDivEnd = strpos($result, '</div>', $actionsDivStart);

            if ($actionsDivStart !== false && $actionsDivEnd !== false) {
                $actionsDivContent = substr($result, $actionsDivStart, $actionsDivEnd - $actionsDivStart);
                $buttonHtml = $this->refundButtonBlock->toHtml();
                $result = str_replace($actionsDivContent, $actionsDivContent . $buttonHtml, $result);
            }
        }
        return $result;
    }
}
