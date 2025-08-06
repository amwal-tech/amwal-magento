<?php
declare(strict_types=1);

namespace Amwal\Payments\Model;

use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\CurrencyConverter;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Store\Model\StoreManagerInterface;

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
     * Currency converter instance
     *
     * @var CurrencyConverter
     */
    private CurrencyConverter $currencyConverter;

    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * Constructor
     *
     * @param Config $config
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ScheduleCollectionFactory $scheduleCollectionFactory
     * @param ModuleListInterface $moduleList
     * @param CurrencyConverter $currencyConverter
     * @param CheckoutSession $checkoutSession
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config                    $config,
        OrderRepositoryInterface  $orderRepository,
        SearchCriteriaBuilder     $searchCriteriaBuilder,
        ScheduleCollectionFactory $scheduleCollectionFactory,
        ModuleListInterface       $moduleList,
        CurrencyConverter         $currencyConverter,
        CheckoutSession           $checkoutSession,
        StoreManagerInterface     $storeManager
    )
    {
        $this->config = $config;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->moduleList = $moduleList;
        $this->currencyConverter = $currencyConverter;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
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
            'bin_discount_rule' => !$this->config->getDiscountRule(),
            'module_type' => $this->config->getModuleType(),
            'apple_pay_active' => $this->config->isApplePayActive(),
            'bank_installments_active' => $this->config->isBankInstallmentsActive(),
            'webhook_enabled' => $this->config->isWebhookEnabled(),
            'webhook_events' => $this->config->getWebhookEvents(),
            'webhook_private_key' => $this->config->getWebhookPrivateKey() ? 'stored' : 'empty',
            'webhook_fingerprint' => $this->config->getApiKeyFingerprint() ? 'stored' : 'empty',
            'webhook_debug' => $this->config->isWebhookDebugMode(),
        ];
        // Fetch pending payment orders count and amwal order ids
        try {
            $pendingPaymentOrders = $this->orderRepository->getList(
                $this->searchCriteriaBuilder
                    ->addFilter('amwal_order_id', '', 'neq')
                    ->addFilter('status', 'pending_payment')
                    ->create()
            );
            $settings['pending_payment_orders'] = $pendingPaymentOrders->getTotalCount();
            $settings['pending_payment_orders_amwal_ids'] = array_map(function ($order) {
                return $order->getAmwalOrderId();
            }, $pendingPaymentOrders->getItems());
        } catch (\Exception $e) {
            $settings['pending_payment_orders'] = 0;
        }
        // Fetch cancelled orders count and amwal order ids
        try {
            $cancelledOrders = $this->orderRepository->getList(
                $this->searchCriteriaBuilder
                    ->addFilter('amwal_order_id', '', 'neq')
                    ->addFilter('status', 'canceled')
                    ->create()
            );
            $settings['cancelled_orders'] = $cancelledOrders->getTotalCount();
            $settings['cancelled_orders_amwal_ids'] = array_map(function ($order) {
                return $order->getAmwalOrderId();
            }, $cancelledOrders->getItems());
        } catch (\Exception $e) {
            $settings['cancelled_orders'] = 0;
        }

        // Fetch cron job information for amwal_pending_orders_update
        $scheduleCollectionPending = $this->scheduleCollectionFactory->create();
        $scheduleCollectionPending->addFieldToFilter('job_code', 'amwal_pending_orders_update');
        $scheduleCollectionPending->setOrder('executed_at', 'desc');
        $schedulePending = $scheduleCollectionPending->getFirstItem();
        if ($schedulePending->getId()) {
            $settings['pending_orders_update']['cronjob_last_run'] = $schedulePending->getExecutedAt();
            $settings['pending_orders_update']['cronjob_status'] = $schedulePending->getStatus();
            $settings['pending_orders_update']['cronjob_status_message'] = $schedulePending->getMessages();
        }

        // Fetch cron job information for amwal_canceled_orders_update
        $scheduleCollectionCanceled = $this->scheduleCollectionFactory->create();
        $scheduleCollectionCanceled->addFieldToFilter('job_code', 'amwal_canceled_orders_update');
        $scheduleCollectionCanceled->setOrder('executed_at', 'desc');
        $scheduleCanceled = $scheduleCollectionCanceled->getFirstItem();
        if ($scheduleCanceled->getId()) {
            $settings['canceled_orders_update']['cronjob_last_run'] = $scheduleCanceled->getExecutedAt();
            $settings['canceled_orders_update']['cronjob_status'] = $scheduleCanceled->getStatus();
            $settings['canceled_orders_update']['cronjob_status_message'] = $scheduleCanceled->getMessages();
        }

        // Retrieve installed modules
        $installedModules = $this->moduleList->getNames();
        $settings['installed_modules'] = $installedModules;

        return [
            'data' => $settings
        ];
    }

    /**
     * Get currency settings for API endpoint (without parameters)
     *
     * @return array
     */
    public function getCurrencySettings(): array
    {
        try {
            $quote = $this->getQuoteOrNull();
            $hasQuote = $quote && $quote->getId();

            $currentCurrency = $hasQuote
                ? ($quote->getQuoteCurrencyCode() ?: $this->config->getDefaultCurrency())
                : $this->storeManager->getStore()->getCurrentCurrencyCode();

            $grandTotal = $hasQuote ? $quote->getGrandTotal() : 0;
            $convertedAmount = $hasQuote ? $this->currencyConverter->convertToSAR(floatval($grandTotal), $quote) : 0;

            return [
                'data' => [
                    'success' => true,
                    'current_currency' => $currentCurrency,
                    'amount' => $convertedAmount,
                    'grand_total' => $grandTotal,
                    'has_quote' => $hasQuote
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => __('Error retrieving currency settings: %1', $e->getMessage())->render()
            ];
        }
    }

    private function getQuoteOrNull()
    {
        try {
            return $this->checkoutSession->getQuote();
        } catch (\Exception $e) {
            return null;
        }
    }
}
