<?php
declare(strict_types=1);

namespace Amwal\Payments\Plugin\Sentry;

use Amwal\Payments\Model\Config;
use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Sentry;
use Sentry\State\Scope;


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
     * @param \Throwable $exception
     * @return void
     */
    public function report(\Throwable $exception): void
    {
        $this->initializeSentrySdk();
        Sentry\configureScope(function (Scope $scope) {
            // Add extra context data to the exception
            $scope->setExtra('domain', $this->scopeConfig->getValue(Custom::XML_PATH_SECURE_BASE_URL) ?? 'runtime cli');
            $scope->setExtra('plugin_type', 'magento2');
            $scope->setExtra('plugin_version', Config::MODULE_VERSION);
            $scope->setExtra('php_version', phpversion());
        });

        // Send exception to Sentry with the hint
        Sentry\captureException($exception);
    }

    /**
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
