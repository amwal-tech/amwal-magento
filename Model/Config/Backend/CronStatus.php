<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;

class CronStatus extends Value
{
    /**
     * @var ScheduleCollectionFactory
     */
    private $scheduleCollectionFactory;

    /**
     * @param ScheduleCollectionFactory $scheduleCollectionFactory
     */
    public function __construct(
        ScheduleCollectionFactory $scheduleCollectionFactory
    ) {
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
    }


    public function afterLoad()
    {
        $collection = $this->scheduleCollectionFactory->create()
            ->addFieldToFilter('job_code', ['eq' => 'amwal_pending_orders_update'])
            ->setOrder('scheduled_at', 'DESC')
            ->setPageSize(1)
            ->setCurPage(1);

        $item = $collection->getFirstItem();

        if ($item && $item->getId()) {
            $status = 'Last Run: ' . $item->getScheduledAt() . ' Status: ' . $item->getStatus();
        } else {
            $status = 'Cron job has not run yet, please check the crontab in your server';
        }
        $this->setValue($status);
    }
}
