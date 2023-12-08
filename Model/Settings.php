<?php
declare(strict_types=1);

namespace Amwal\Payments\Model;

use Amwal\Payments\Model\Config;

class Settings
{
    /**
     * @var Config
     */
    private Config $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Retrieves settings.
     *
     * @return array An associative array of settings.
     */
    public function getSettings() : array
    {
        $settings = [
            'amwal_payment' => $this->config->isActive(),
            'amwal_payment_title' => $this->config->getTitle(),
            'is_merchant_valida' => $this->config->isMerchantValid(),
            'express_checkout_active' => $this->config->isExpressCheckoutActive(),
            'regular_checkout_active' => $this->config->isRegularCheckoutActive(),
            'country' => $this->config->getCountryCode(),
            'default_order_status' => $this->config->getOrderConfirmedStatus(),
            'create_user_on_order' => $this->config->shouldCreateCustomer(),
            'order_status_changed_customer_email' => $this->config->isOrderStatusChangedCustomerEmailEnabled(),
            'order_status_changed_admin_email' => $this->config->isOrderStatusChangedAdminEmailEnabled(),
            'cronjob_enable' => $this->config->isCronjobEnabled(),
            'debug' => $this->config->isDebugModeEnabled(),
            'sentry' => $this->config->isSentryReportEnabled()
        ];
        return $settings;
    }
}
