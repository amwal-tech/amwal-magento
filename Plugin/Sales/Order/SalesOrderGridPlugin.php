<?php

namespace Amwal\Payments\Plugin\Sales\Order;

use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class SalesOrderGridPlugin
{
    /**
     * @param Collection $subject
     * @return null
     */
    public function beforeLoad(Collection $subject)
    {
        if (!$subject->isLoaded()) {
            $subject->getSelect()->joinLeft(
                ['sales_order' => $subject->getTable('sales_order')],
                'main_table.entity_id = sales_order.entity_id',
                ['amwal_order_id']
            );
        }
        return null;
    }
}
