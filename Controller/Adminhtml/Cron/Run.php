<?php
declare(strict_types=1);

namespace Amwal\Payments\Controller\Adminhtml\Cron;

use Amwal\Payments\Cron\CanceledOrdersUpdate;
use Amwal\Payments\Cron\PendingOrdersUpdate;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Psr\Log\LoggerInterface;

class Run extends Action
{
    public const ADMIN_RESOURCE = 'Amwal_Payments::config';

    private JsonFactory $resultJsonFactory;
    private FormKeyValidator $formKeyValidator;
    private PendingOrdersUpdate $pendingOrdersUpdate;
    private CanceledOrdersUpdate $canceledOrdersUpdate;
    private LoggerInterface $logger;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        FormKeyValidator $formKeyValidator,
        PendingOrdersUpdate $pendingOrdersUpdate,
        CanceledOrdersUpdate $canceledOrdersUpdate,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->pendingOrdersUpdate = $pendingOrdersUpdate;
        $this->canceledOrdersUpdate = $canceledOrdersUpdate;
        $this->logger = $logger;
    }

    public function execute(): Json
    {
        $result = $this->resultJsonFactory->create();

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $result->setData([
                'success' => false,
                'message' => __('Invalid form key. Please refresh the page and try again.')
            ]);
        }

        $job = (string)$this->getRequest()->getParam('job', 'all');
        $executedJobs = [];

        try {
            if ($job === 'pending' || $job === 'all') {
                $this->pendingOrdersUpdate->execute();
                $executedJobs[] = __('Pending Orders');
            }

            if ($job === 'canceled' || $job === 'all') {
                $this->canceledOrdersUpdate->execute();
                $executedJobs[] = __('Canceled Orders');
            }

            return $result->setData([
                'success' => true,
                'message' => __('Successfully ran synchronization for %1.', implode(' & ', $executedJobs)),
                'timestamp' => gmdate('Y-m-d H:i:s') . ' UTC'
            ]);
        } catch (Exception $e) {
            $this->logger->error(sprintf('Error running cron manually: %s', $e->getMessage()));
            return $result->setData([
                'success' => false,
                'message' => __('Failed to execute cron job: %1', $e->getMessage())
            ]);
        }
    }
}
