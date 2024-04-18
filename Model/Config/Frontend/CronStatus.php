<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Config\Frontend;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;

class CronStatus extends Field
{
    /**
     * @var ScheduleCollectionFactory
     */
    private $scheduleCollectionFactory;

    /**
     * Constructor
     *
     * @param ScheduleCollectionFactory $scheduleCollectionFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        ScheduleCollectionFactory $scheduleCollectionFactory,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $collection = $this->scheduleCollectionFactory->create()
            ->addFieldToFilter('job_code', ['eq' => 'amwal_pending_orders_update'])
            ->setOrder('executed_at', 'DESC')
            ->setPageSize(1)
            ->setCurPage(1);

        $item = $collection->getFirstItem();

        if ($item && $item->getId()) {
            $scheduledAt = $item->getScheduledAt();
            $executedAt = $item->getExecutedAt();
            if($scheduledAt){
                $nextRun = new \DateTime($scheduledAt);
                $nextRunFormatted = $nextRun->modify('+1 Hour')->format('Y-m-d H:i:s T');
            }else{
                $nextRunFormatted = 'Not Scheduled';
            }
            if($executedAt){
                $lastRun = new \DateTime($executedAt);
                $lastRunFormatted = $lastRun->format('Y-m-d H:i:s T');
            }else{
                $lastRunFormatted = 'Not Executed';
            }
            $status = __(
                'Last Run: %1 - Next Run:  %2 - Status: %3',
                $lastRunFormatted,
                $nextRunFormatted,
                $item->getStatus()
            );
            $item->getMessages() ? $status .= ' <br> Messages: ' . $item->getMessages() : '';
        } else {
            $status = 'Cron job has not run yet, please check the crontab in your server';
        }

        return '<div id="' . $element->getHtmlId() . '">' . $status . '</div>';
    }
}
