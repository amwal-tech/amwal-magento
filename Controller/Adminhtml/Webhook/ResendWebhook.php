<?php
/**
 * Amwal Payments — Resend Webhook controller
 *
 * Proxies the resend-webhook call to the Amwal backend so the
 * merchant secret key is never exposed to the browser.
 */
declare(strict_types=1);

namespace Amwal\Payments\Controller\Adminhtml\Webhook;

use Amwal\Payments\Model\AmwalClientFactory;
use Amwal\Payments\Model\Config;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

/**
 * Resend Webhook admin controller
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResendWebhook extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Amwal_Payments::config';

    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @var AmwalClientFactory
     */
    private AmwalClientFactory $amwalClientFactory;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @param Context            $context
     * @param JsonFactory        $resultJsonFactory
     * @param AmwalClientFactory $amwalClientFactory
     * @param Config             $config
     * @param EncryptorInterface $encryptor
     * @param LoggerInterface    $logger
     * @param Json               $json
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        AmwalClientFactory $amwalClientFactory,
        Config $config,
        EncryptorInterface $encryptor,
        LoggerInterface $logger,
        Json $json,
        ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->amwalClientFactory = $amwalClientFactory;
        $this->config = $config;
        $this->encryptor = $encryptor;
        $this->logger = $logger;
        $this->json = $json;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Execute resend webhook action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $amwalOrderId = $this->getRequest()->getParam('amwal_order_id');
        $magentoOrderId = $this->getRequest()->getParam('magento_order_id', '');

        if (empty($amwalOrderId)) {
            $this->logger->warning('Amwal ResendWebhook: Missing amwal_order_id parameter.');
            return $result->setData([
                'success' => false,
                'message' => (string) __('Amwal Order ID is required.')
            ]);
        }

        $this->logger->info(sprintf(
            'Amwal ResendWebhook: Admin initiated resend webhook for Amwal Order ID "%s" (Magento Order: %s)',
            $amwalOrderId,
            $magentoOrderId ?: 'N/A'
        ));

        try {
            $apiResult = $this->sendResendRequest($amwalOrderId);
            $statusCode = $apiResult['statusCode'];
            $responseBody = $apiResult['responseBody'];
            $responseData = $apiResult['responseData'];

            $this->logger->info(sprintf(
                'Amwal ResendWebhook: Response for Amwal Order ID "%s" — HTTP %d — %s',
                $amwalOrderId,
                $statusCode,
                $responseBody
            ));

            // Log to amwal_webhook_log table
            $this->logResendAction($amwalOrderId, $magentoOrderId, $statusCode, $responseBody);

            if ($statusCode >= 200 && $statusCode < 300) {
                return $result->setData([
                    'success' => true,
                    'message' => (string) __('Webhook resent successfully for Amwal Order %1.', $amwalOrderId),
                    'response' => $responseData
                ]);
            } else {
                return $result->setData([
                    'success' => false,
                    'message' => (string) __('Amwal API returned HTTP %1. Response: %2', $statusCode, $responseBody),
                    'response' => $responseData
                ]);
            }
        } catch (GuzzleException $e) {
            $errorMessage = $e->getMessage();
            $this->logger->error(sprintf(
                'Amwal ResendWebhook: GuzzleException for Amwal Order ID "%s" — %s',
                $amwalOrderId,
                $errorMessage
            ));

            // Log the failure
            $this->logResendAction($amwalOrderId, $magentoOrderId, 0, $errorMessage, false);

            return $result->setData([
                'success' => false,
                'message' => (string) __('Failed to resend webhook: %1', $errorMessage)
            ]);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->logger->error(sprintf(
                'Amwal ResendWebhook: Exception for Amwal Order ID "%s" — %s',
                $amwalOrderId,
                $errorMessage
            ));

            $this->logResendAction($amwalOrderId, $magentoOrderId, 0, $errorMessage, false);

            return $result->setData([
                'success' => false,
                'message' => (string) __('An error occurred: %1', $errorMessage)
            ]);
        }
    }

    /**
     * Send the resend webhook request to Amwal API.
     *
     * @param string $amwalOrderId
     * @return array{statusCode: int, responseBody: string, responseData: array}
     * @throws LocalizedException
     * @throws GuzzleException
     */
    private function sendResendRequest(string $amwalOrderId): array
    {
        $decryptedKey = $this->encryptor->decrypt($this->config->getSecretKey());

        if (empty($decryptedKey)) {
            $this->logger->error('Amwal ResendWebhook: Secret key is not configured.');
            throw new LocalizedException(
                __('Amwal secret key is not configured. Please set it in Stores > Configuration > Payment Methods > Amwal.')
            );
        }

        $amwalClient = $this->amwalClientFactory->create();
        $endpoint = 'transactions/' . $amwalOrderId . '/resend_webhook/';
        $response = $amwalClient->post($endpoint, [
            RequestOptions::JSON => new \stdClass(), // sends {}
            RequestOptions::HEADERS => [
                'Authorization' => $decryptedKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ]);

        $statusCode = $response->getStatusCode();
        $responseBody = $response->getBody()->getContents();

        try {
            $responseData = $this->json->unserialize($responseBody);
        } catch (\Exception $e) {
            $responseData = ['raw' => $responseBody];
        }

        return ['statusCode' => $statusCode, 'responseBody' => $responseBody, 'responseData' => $responseData];
    }

    /**
     * Log the resend webhook action to the amwal_webhook_log table.
     *
     * @param string $amwalOrderId
     * @param string $magentoOrderId
     * @param int    $statusCode
     * @param string $responseBody
     * @param bool   $success
     * @return void
     */
    private function logResendAction(
        string $amwalOrderId,
        string $magentoOrderId,
        int $statusCode,
        string $responseBody,
        bool $success = true
    ): void {
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('amwal_webhook_log');

            // Check if the table exists before inserting
            if (!$connection->isTableExists($tableName)) {
                $this->logger->warning('Amwal ResendWebhook: amwal_webhook_log table does not exist, skipping log.');
                return;
            }

            $adminUser = $this->_auth->getUser();
            $adminUsername = $adminUser ? $adminUser->getUserName() : 'unknown';

            $connection->insert($tableName, [
                'event_type' => 'webhook.resend',
                'order_id' => $amwalOrderId,
                'magento_order_id' => $magentoOrderId,
                'signature_verified' => 1,
                'success' => $success ? 1 : 0,
                'message' => sprintf(
                    'Admin "%s" triggered webhook resend. HTTP %d. Response: %s',
                    $adminUsername,
                    $statusCode,
                    mb_substr($responseBody, 0, 500)
                ),
                'payload' => $this->json->serialize([
                    'action' => 'resend_webhook',
                    'amwal_order_id' => $amwalOrderId,
                    'magento_order_id' => $magentoOrderId,
                    'admin_user' => $adminUsername,
                    'http_status' => $statusCode,
                    'response' => mb_substr($responseBody, 0, 1000),
                    'timestamp' => date('c'),
                ]),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Amwal ResendWebhook: Failed to log resend action — ' . $e->getMessage());
        }
    }
}

