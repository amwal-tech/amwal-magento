<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\App\ResourceConnection;

class CronStatus extends Value
{
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }
    public function afterLoad(){
        $resource = $this->resourceConnection;
        $connection = $resource->getConnection();

        $tableName = $resource->getTableName('cron_schedule');
        $query = 'SELECT * FROM ' . $tableName . ' WHERE job_code = "amwal_pending_orders_update" ORDER BY scheduled_at DESC LIMIT 1';
        $result = $connection->fetchRow($query);

        if ($result) {
            $status = 'Last Run: ' . $result['scheduled_at'] . ' Status: ' . $result['status'];
        } else {
            $status = 'Cron job has not run yet.';
        }

        $this->setValue($status);
    }
}
