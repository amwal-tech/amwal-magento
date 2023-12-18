<?php
declare(strict_types=1);

namespace Amwal\Payments\Plugin\Sentry;

use Sentry;
use Sentry\State\Scope;
use Amwal\Payments\Model\Config;

class SentryExceptionReport
{
    /**
     * @var Config
     */
    protected Config $config;

    /**
     * SentryExceptionReport constructor.
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        // Initialize Sentry SDK
        if (!class_exists(Sentry\ClientBuilder::class) || !$this->config->isSentryReportEnabled()) {
            return;
        }
        Sentry\init(['dsn' => 'https://1fe7bb63698145909bb12240e03fa59e@sentry.amwal.dev/5']);
    }
    /**
     * @param \Throwable $exception
     * @return void
     */
    public function report(\Throwable $exception): void
    {
        if (!class_exists(Sentry\ClientBuilder::class) || !$this->config->isSentryReportEnabled()) {
            return;
        }

        Sentry\configureScope(function (Scope $scope) {
            // Add extra context data to the exception
            $scope->setExtra('domain', $_SERVER['HTTP_HOST'] ?? 'runtime cli');
            $scope->setExtra('plugin_type', 'magento2');
            $scope->setExtra('plugin_version', Config::MODULE_VERSION);
            $scope->setExtra('php_version', phpversion());
        });

        // Send exception to Sentry with the hint
        Sentry\captureException($exception);
    }
}
