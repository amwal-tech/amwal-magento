<?php
declare(strict_types=1);

namespace Amwal\Payments\Model;

use Amwal\Payments\Model\Config;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;
use Magento\Framework\Module\ModuleListInterface;

class Settings
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var ScheduleCollectionFactory
     */
    private ScheduleCollectionFactory $scheduleCollectionFactory;


    /**
     * @var ModuleListInterface
     */
    private ModuleListInterface $moduleList;

    /**
     * Constructor
     *
     * @param Config $config
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ScheduleCollectionFactory $scheduleCollectionFactory
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        Config $config,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ScheduleCollectionFactory $scheduleCollectionFactory,
        ModuleListInterface $moduleList
    ) {
        $this->config = $config;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->moduleList = $moduleList;
    }

    /**
     * Retrieves settings.
     *
     * @return array
     */
    public function getSettings(): array
    {
        $settings = [
            'amwal_payment' => $this->config->isActive(),
            'amwal_payment_title' => $this->config->getTitle(),
            'is_merchant_valid' => $this->config->isMerchantValid(),
            'express_checkout_active' => $this->config->isExpressCheckoutActive(),
            'regular_checkout_active' => $this->config->isRegularCheckoutActive(),
            'country' => $this->config->getCountryCode(),
            'default_order_status' => $this->config->getOrderConfirmedStatus(),
            'create_user_on_order' => $this->config->shouldCreateCustomer(),
            'order_status_changed_customer_email' => $this->config->isOrderStatusChangedCustomerEmailEnabled(),
            'order_status_changed_admin_email' => $this->config->isOrderStatusChangedAdminEmailEnabled(),
            'cronjob_enable' => $this->config->isCronjobEnabled(),
            'debug' => $this->config->isDebugModeEnabled(),
            'sentry' => $this->config->isSentryReportEnabled(),
            'discount_ribbon' => $this->config->isDiscountRibbonEnabled(),
            'pwa' => $this->config->isPwaMode(),
            'bank_installments' => $this->config->isBankInstallmentsEnabled(),
            'magagento_version' => $this->config->getMagentoVersion(),
            'php_version' => $this->config->getPhpVersion(),
            'version' => $this->config->getVersion(),
        ];

        try {
            $pendingPaymentOrders = $this->orderRepository->getList(
                $this->searchCriteriaBuilder
                    ->addFilter('payment_method', 'amwal_payment')
                    ->addFilter('status', 'pending_payment')
                    ->create()
            );
            $settings['pending_payment_orders'] = $pendingPaymentOrders->getTotalCount();
        } catch (\Exception $e) {
            $settings['pending_payment_orders'] = 0;
        }

        // Fetch cron job information
        $scheduleCollection = $this->scheduleCollectionFactory->create();
        $scheduleCollection->addFieldToFilter('job_code', 'amwal_pending_orders_update');
        $scheduleCollection->setOrder('executed_at', 'desc');
        $schedule = $scheduleCollection->getFirstItem();
        if ($schedule) {
            $settings['cronjob_last_run'] = $schedule->getExecutedAt();
            $settings['cronjob_status'] = $schedule->getStatus();
            $settings['cronjob_status_message'] = $schedule->getMessages();
        }

        // Retrieve installed modules
        $installedModules = $this->moduleList->getNames();
        $settings['installed_modules'] = $installedModules;

        return [
            'data' => $settings
        ];
    }
}
