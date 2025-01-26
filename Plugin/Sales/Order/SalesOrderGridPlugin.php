<?php

namespace Amwal\Payments\Plugin\Sales\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class SalesOrderGridPlugin
{
    /**
     * Join the fields to render the value in order grid
     *
     * @param Collection $subject
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     * @throws LocalizedException
     */
    public function beforeLoad(Collection $subject, bool $printQuery = false, bool $logQuery = false): array
    {
        if (!$subject->isLoaded()) {
            $primaryKey = $subject->getResource()->getIdFieldName();
            $salesOrderTable = $subject->getResource()->getTable('sales_order');

            $subject->getSelect()->joinLeft(
                ['sales_order' => $salesOrderTable],
                $subject->getConnection()->quoteInto(
                    'main_table.' . $primaryKey . ' = sales_order.entity_id AND sales_order.amwal_order_id IS NOT NULL',
                    []
                ),
                [
                    'amwal_order_id' => 'sales_order.amwal_order_id',
                    'amwal_trigger_context' => 'sales_order.amwal_trigger_context'
                ]
            )->distinct(true);
        }

        return [$printQuery, $logQuery];
    }
}
