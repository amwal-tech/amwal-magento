<?php
declare(strict_types=1);

namespace Amwal\Payments\Plugin\Sentry;

use Amwal\Payments\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var bool
     */
    private bool $isInitialized = false;

    /**
     * SentryExceptionReport constructor.
     *
     * @param Config $config
     * @param ScopeConfigInterface $scopeConfig
     * @param State $state
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Config $config,
        ScopeConfigInterface $scopeConfig,
        State $state,
        ?LoggerInterface $logger = null
    ) {
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
        $this->state = $state;
        $this->logger = $logger ?? \Magento\Framework\App\ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Intercept and report uncaught exceptions from Magento HTTP application.
     * Only reports exceptions that originate from Amwal code.
     *
     * @param \Magento\Framework\App\Http $subject
     * @param \Magento\Framework\App\Bootstrap $bootstrap
     * @param \Exception $exception
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCatchException(
        \Magento\Framework\App\Http $subject,
        \Magento\Framework\App\Bootstrap $bootstrap,
        \Exception $exception
    ): void {
        if ($this->isAmwalException($exception)) {
            $this->report($exception);
        }
    }

    /**
     * Determine if an exception originates from Amwal code.
     *
     * Checks the exception class namespace, the file where it was thrown,
     * and the stack trace for any Amwal\Payments origin.
     *
     * @param \Throwable $exception
     * @return bool
     */
    private function isAmwalException(\Throwable $exception): bool
    {
        // Check if the exception class itself belongs to the Amwal namespace
        if (str_starts_with(get_class($exception), 'Amwal\\')) {
            return true;
        }

        // Check if the exception was thrown from an Amwal file
        $file = $exception->getFile();
        if ($this->isAmwalPath($file)) {
            return true;
        }

        // Check the stack trace for Amwal origin
        foreach ($exception->getTrace() as $frame) {
            if (isset($frame['class']) && str_starts_with($frame['class'], 'Amwal\\')) {
                return true;
            }
            if (isset($frame['file']) && $this->isAmwalPath($frame['file'])) {
                return true;
            }
        }

        // Check previous/chained exceptions
        $previous = $exception->getPrevious();
        if ($previous !== null) {
            return $this->isAmwalException($previous);
        }

        return false;
    }

    /**
     * Check if a file path belongs to the Amwal module.
     *
     * @param string $file
     * @return bool
     */
    private function isAmwalPath(string $file): bool
    {
        return stripos($file, '/vendor/amwal/') !== false
            || stripos($file, '/app/code/Amwal/') !== false
            || stripos($file, '\\vendor\\amwal\\') !== false
            || stripos($file, '\\app\\code\\Amwal\\') !== false;
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

        try {
            // Check if Sentry functions are available
            if (!function_exists('\Sentry\captureException')) {
                return;
            }

            \Sentry\captureException($exception);
        } catch (\Throwable $e) {
            // Silently fail if Sentry reporting fails
            $this->logger->error('Sentry reporting failed: ' . $e->getMessage());
        }
    }

    /**
     * Set tags for Sentry reporting
     *
     * @param array<string, mixed>|string $tags Array of key-value pairs or single tag key
     * @param string|int|float|null $value Tag value if $tags is a string
     * @return bool True if tags were set successfully, false otherwise
     */
    public function setTags($tags, $value = null): bool
    {
        if (!$this->initializeSentrySDK()) {
            return false;
        }

        try {
            // Check if Sentry functions are available
            if (!function_exists('\Sentry\configureScope')) {
                return false;
            }

            // Sentry SDK v4
            \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($tags, $value): void {
                $this->setScopeTags($scope, $tags, $value);
            });

            return true;
        } catch (\Throwable $e) {
            // Silently fail if Sentry reporting fails
            $this->logger->error('Sentry tag setting failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Set scope extras for Sentry
     *
     * @param \Sentry\State\Scope $scope
     */
    private function setScopeExtras(\Sentry\State\Scope $scope): void
    {
        $domain = $this->scopeConfig->getValue(Store::XML_PATH_SECURE_BASE_URL);
        $scope->setExtra('domain', !empty($domain) ? $domain : 'runtime cli');
        $scope->setExtra('plugin_type', 'magento2');
        $scope->setExtra('plugin_version', Config::MODULE_VERSION);
        $scope->setExtra('php_version', phpversion());
    }

    /**
     * Set scope tags for Sentry
     *
     * @param \Sentry\State\Scope $scope
     * @param array<string, mixed>|string $tags
     * @param string|int|float|null $value
     */
    private function setScopeTags(\Sentry\State\Scope $scope, $tags, $value = null): void
    {
        if (is_string($tags) && $value !== null) {
            // Single tag
            $scope->setTag($tags, (string)$value);
        } elseif (is_array($tags)) {
            // Multiple tags
            foreach ($tags as $key => $val) {
                if (is_string($key) && (is_string($val) || is_numeric($val))) {
                    $scope->setTag($key, (string)$val);
                }
            }
        }
    }

    /**
     * Initialize the Sentry SDK
     *
     * @return bool
     */
    private function initializeSentrySDK(): bool
    {
        if ($this->isInitialized) {
            return true;
        }

        // Check if Sentry reporting is enabled
        if (!$this->config->isSentryReportEnabled()) {
            return false;
        }

        // Check if Sentry SDK is available
        if (!function_exists('\Sentry\init')) {
            return false;
        }

        try {
            // Initialize Sentry with error handling
            \Sentry\init([
                'dsn' => 'https://17c9ec84f8878111e96dd6b9609032c4@o4509389080690688.ingest.us.sentry.io/4512123275313152',
                'environment' => $this->state->getMode(),
                'release' => 'amwal-magento@' . Config::MODULE_VERSION,
                'error_types' => E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED,
                // Prevent Sentry from overriding Magento's global error/exception handlers
                'integrations' => static function (array $integrations): array {
                    $excluded = [
                        'Sentry\Integration\ErrorListenerIntegration',
                        'Sentry\Integration\ExceptionListenerIntegration',
                        'Sentry\Integration\FatalErrorListenerIntegration',
                    ];
                    return array_filter($integrations, static function ($integration) use ($excluded): bool {
                        return !in_array(get_class($integration), $excluded, true);
                    });
                },
            ]);

            // Set scope extras once upon initialization
            if (function_exists('\Sentry\configureScope')) {
                \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
                    $this->setScopeExtras($scope);
                });
            }

            $this->isInitialized = true;
            return true;
        } catch (\Throwable $e) {
            // Log initialization failure
            $this->logger->error('Sentry SDK initialization failed: ' . $e->getMessage());
            return false;
        }
    }
}

