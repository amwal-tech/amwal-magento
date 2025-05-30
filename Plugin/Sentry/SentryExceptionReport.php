<?php
declare(strict_types=1);

namespace Amwal\Payments\Plugin\Sentry;

use Amwal\Payments\Model\Config;
use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Sentry;
use Sentry\State\Scope;
use Sentry\Sdk;

class SentryExceptionReport
{
    /**
     * @var Config
     */
    private Config $config;
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;
    /**
     * @var State
     */
    private State $state;

    /**
     * SentryExceptionReport constructor.
     *
     * @param Config $config
     * @param ScopeConfigInterface $scopeConfig
     * @param State $state
     */
    public function __construct(
        Config $config,
        ScopeConfigInterface $scopeConfig,
        State $state
    ) {
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
        $this->state = $state;
    }

    /**
     * Report an exception to Sentry
     *
     * @param \Throwable $exception
     */
    public function report(\Throwable $exception): void
    {
        if (!$this->initializeSentrySDK()) {
            return;
        }

        Sdk::getCurrentHub()->configureScope(function (Scope $scope) {
            $scope->setExtra('domain', $this->scopeConfig->getValue(Custom::XML_PATH_SECURE_BASE_URL) ?? 'runtime cli');
            $scope->setExtra('plugin_type', 'magento2');
            $scope->setExtra('plugin_version', Config::MODULE_VERSION);
            $scope->setExtra('php_version', phpversion());
        });

        Sentry\captureException($exception);
    }

    /**
     * Initialize the Sentry SDK
     *
     * @return bool
     */
    private function initializeSentrySDK(): bool
    {
        if (!class_exists(Sentry\ClientBuilder::class) || !$this->config->isSentryReportEnabled()) {
            return false;
        }

        Sentry\init(['dsn' => 'https://1fe7bb63698145909bb12240e03fa59e@sentry.amwal.dev/5']);
        return true;
    }
}
