<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Checkout;

use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\ErrorReporter;
use Psr\Log\LoggerInterface;

abstract class AmwalCheckoutAction
{
    public const IS_AMWAL_API_CALL  = 'is_amwal_api_call';

    protected ErrorReporter $errorReporter;
    protected Config $config;
    protected LoggerInterface $logger;

    public function __construct(
        ErrorReporter $errorReporter,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->errorReporter = $errorReporter;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param string $amwalOrderId
     * @param string|array $message
     * @return void
     */
    protected function reportError(string $amwalOrderId, $message): void
    {
        if (!is_array($message)) {
            $message = ['message' => $message];
        }

        $this->errorReporter->execute($amwalOrderId, $message);
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logDebug(string $message, array $context = []): void
    {
        if (!$this->config->isDebugModeEnabled()) {
            return;
        }

        $this->logger->debug($message, $context);
    }
}
