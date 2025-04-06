<?php
namespace Amwal\Payments\Model\Event;

use Magento\Sales\Model\Order;

/**
 * Interface for webhook event handlers
 */
interface HandlerInterface
{
    /**
     * Execute handler with order and webhook data
     *
     * @param Order $order
     * @param array $data
     * @return void
     */
    public function execute(Order $order, array $data);
}
