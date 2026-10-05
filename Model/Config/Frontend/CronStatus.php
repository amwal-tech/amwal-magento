<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Config\Frontend;

use Amwal\Payments\Model\Config;
use DateTime;
use DateTimeZone;
use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class CronStatus extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Amwal_Payments::system/config/cron_status.phtml';

    private ScheduleCollectionFactory $scheduleCollectionFactory;
    private OrderCollectionFactory $orderCollectionFactory;
    private Config $config;

    /**
     * @param Context $context
     * @param ScheduleCollectionFactory $scheduleCollectionFactory
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        ScheduleCollectionFactory $scheduleCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->config = $config;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->addData([
            'html_id' => $element->getHtmlId(),
            'ajax_url' => $this->_urlBuilder->getUrl('amwal/cron/run'),
            'pending_job' => $this->getJob('amwal_pending_orders_update', (string)__('Pending Orders')),
            'canceled_job' => $this->getJob('amwal_canceled_orders_update', (string)__('Canceled Orders')),
        ]);

        return $this->_toHtml();
    }

    /**
     * Retrieve polished status details for a given job code
     *
     * @param string $jobCode
     * @param string $title
     * @return array
     */
    public function getJob(string $jobCode, string $title): array
    {
        $item = $this->getLatestScheduleItem($jobCode);
        $statusDetails = $this->resolveStatusDetails($item);
        $queueCount = $this->resolveQueueCount($jobCode);

        return [
            'code' => $jobCode,
            'title' => $title,
            'status_text' => $statusDetails['text'],
            'status_class' => $statusDetails['class'],
            'status_modifier' => $statusDetails['modifier'],
            'last_run' => $this->resolveLastRun($item),
            'queue_count' => $queueCount,
            'queue_text' => $this->formatQueueText($queueCount),
        ];
    }

    /**
     * Retrieve the most recent completed or running schedule item for a job
     *
     * @param string $jobCode
     * @return \Magento\Cron\Model\Schedule|null
     */
    private function getLatestScheduleItem(string $jobCode)
    {
        $collection = $this->scheduleCollectionFactory->create()
            ->addFieldToFilter('job_code', $jobCode)
            ->addFieldToFilter('status', ['neq' => 'pending'])
            ->setOrder('scheduled_at', 'DESC')
            ->setPageSize(1);

        $item = $collection->getFirstItem();
        return ($item && $item->getId()) ? $item : null;
    }

    /**
     * Resolve status label and CSS classes from schedule item
     *
     * @param \Magento\Cron\Model\Schedule|null $item
     * @return array
     */
    private function resolveStatusDetails($item): array
    {
        if (!$item) {
            return [
                'text' => (string)__('Not Run Yet'),
                'class' => 'amwal-status-dot--neutral',
                'modifier' => 'muted',
            ];
        }

        $status = (string)$item->getStatus();
        $statusMap = [
            'success' => [
                'text' => (string)__('Active'),
                'class' => 'amwal-status-dot--success',
                'modifier' => 'active',
            ],
            'running' => [
                'text' => (string)__('Running'),
                'class' => 'amwal-status-dot--running',
                'modifier' => 'active',
            ],
            'error' => [
                'text' => (string)__('Error'),
                'class' => 'amwal-status-dot--error',
                'modifier' => 'error',
            ],
        ];

        return $statusMap[$status] ?? [
            'text' => ucfirst($status),
            'class' => 'amwal-status-dot--neutral',
            'modifier' => 'muted',
        ];
    }

    /**
     * Resolve formatted last run text from schedule item
     *
     * @param \Magento\Cron\Model\Schedule|null $item
     * @return string
     */
    private function resolveLastRun($item): string
    {
        if (!$item) {
            return (string)__('Never');
        }

        $time = $item->getExecutedAt() ?: $item->getScheduledAt();
        return $time ? $this->formatLastRun($time) : (string)__('Never');
    }

    /**
     * Resolve queue count for a specific job code
     *
     * @param string $jobCode
     * @return int
     */
    private function resolveQueueCount(string $jobCode): int
    {
        if ($jobCode === 'amwal_pending_orders_update') {
            return $this->getPendingOrdersCount();
        }

        if ($jobCode === 'amwal_canceled_orders_update') {
            return $this->getCanceledOrdersCount();
        }

        return 0;
    }

    /**
     * Format last run into concise, readable string
     *
     * @param string $datetime
     * @return string
     */
    public function formatLastRun(string $datetime): string
    {
        try {
            $time = new DateTime($datetime, new DateTimeZone('UTC'));
            $now = new DateTime('now', new DateTimeZone('UTC'));
            $diff = $now->getTimestamp() - $time->getTimestamp();
            $timeStr = $time->format('H:i') . ' UTC';

            if ($diff < 60) {
                return (string)__('Just now (%1)', $timeStr);
            } elseif ($diff < 3600) {
                $mins = (int) floor($diff / 60);
                return (string)__('%1m ago (%2)', $mins, $timeStr);
            } elseif ($diff < 86400) {
                $hours = (int) floor($diff / 3600);
                return (string)__('%1h ago (%2)', $hours, $timeStr);
            } else {
                return $time->format('M j, H:i') . ' UTC';
            }
        } catch (Exception $e) {
            return $datetime;
        }
    }

    /**
     * Format queue count text
     *
     * @param int $count
     * @return string
     */
    public function formatQueueText(int $count): string
    {
        if ($count === 0) {
            return (string)__('Up to date');
        }
        return (string)__('%1 in queue', $count);
    }

    /**
     * Count pending payment orders with Amwal order ID within the active 24-hour sync window
     *
     * @return int
     */
    public function getPendingOrdersCount(): int
    {
        try {
            $fromTime = gmdate('Y-m-d H:i:s', strtotime('-24 hours'));
            return (int) $this->orderCollectionFactory->create()
                ->addFieldToFilter('status', Order::STATE_PENDING_PAYMENT)
                ->addFieldToFilter('amwal_order_id', ['notnull' => true])
                ->addFieldToFilter('created_at', ['gt' => $fromTime])
                ->getSize();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Count canceled orders with Amwal order ID not marked canceled within the active 24-hour sync window
     *
     * @return int
     */
    public function getCanceledOrdersCount(): int
    {
        try {
            $fromTime = gmdate('Y-m-d H:i:s', strtotime('-24 hours'));
            return (int) $this->orderCollectionFactory->create()
                ->addFieldToFilter('status', Order::STATE_CANCELED)
                ->addFieldToFilter('amwal_order_id', ['notnull' => true])
                ->addFieldToFilter('is_amwal_order_canceled', 0)
                ->addFieldToFilter('created_at', ['gt' => $fromTime])
                ->getSize();
        } catch (Exception $e) {
            return 0;
        }
    }
}
