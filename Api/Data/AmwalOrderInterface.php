<?php
declare(strict_types=1);

namespace Amwal\Payments\Api\Data;

interface AmwalOrderInterface
{
    /**
     * Get order details by amwal order ID.
     *
     * @param string $amwalOrderId
     * @return array
     */
    public function getOrderDetails($amwalOrderId);


    /**
     * Update order status by order ID and new status.
     *
     * @return bool
     */
    public function updateOrderStatus();
}
