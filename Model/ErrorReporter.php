<?php
declare(strict_types=1);

namespace Amwal\Payments\Model;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;

class ErrorReporter
{
    /**
     * @var AmwalClientFactory
     */
    private $amwalClientFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param AmwalClientFactory $amwalClientFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        AmwalClientFactory $amwalClientFactory,
        LoggerInterface $logger
    ) {
        $this->amwalClientFactory = $amwalClientFactory;
        $this->logger = $logger;
    }

    /**
     * @param string $amwalOrderId
     * @param array $report
     * @return void
     */
    public function execute(string $amwalOrderId, array $report): void
    {
        $amwalClient = $this->amwalClientFactory->create();

        try {
            $amwalClient->post(
                'transactions/' . $amwalOrderId . '/report_error',
                [RequestOptions::JSON => $report]
            );
        } catch (GuzzleException $e) {
            $this->logger->error(sprintf(
                'Unable to send error report to Amwal. Exception: %s',
                $e->getMessage()
            ));
        }
    }
}
