<?php

namespace Amwal\Payments\Model;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Serialize\Serializer\Base64Json;

/**
 * Webhook helper class for common webhook functions
 */
class WebhookHelper extends AbstractHelper
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var Base64Json
     */
    protected $base64Json;

    /**
     * @param Context $context
     * @param ResourceConnection $resource
     * @param Json $json
     * @param Base64Json $base64Json
     */
    public function __construct(
        Context $context,
        ResourceConnection $resource,
        Json $json,
        Base64Json $base64Json
    ) {
        parent::__construct($context);
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->json = $json;
        $this->base64Json = $base64Json;
    }

    /**
     * Get supported webhook events
     *
     * @return array
     */
    public function getWebhookEvents()
    {
        return [
            'order.created' => 'Order Created',
            'order.success' => 'Order Success',
            'order.failed' => 'Order Failed',
            'order.updated' => 'Order Updated'
        ];
    }

    /**
     * Log webhook request to database
     *
     * @param string $eventType
     * @param array|string $payload
     * @param string|null $apiKeyFingerprint
     * @param bool $signatureVerified
     * @param string|null $orderId
     * @param string|null $magentoOrderId
     * @param bool $success
     * @param string|null $message
     * @return int|bool
     */
    public function logWebhook(
        $eventType,
        $payload,
        $apiKeyFingerprint = null,
        $signatureVerified = false,
        $orderId = null,
        $magentoOrderId = null,
        $success = false,
        $message = null
    ) {
        try {
            // Convert payload to JSON if it's an array
            if (is_array($payload)) {
                $payload = $this->json->serialize($payload);
            }

            // Insert log into database
            $data = [
                'event_type' => $eventType,
                'payload' => $payload,
                'api_key_fingerprint' => $apiKeyFingerprint,
                'signature_verified' => $signatureVerified ? 1 : 0,
                'order_id' => $orderId,
                'magento_order_id' => $magentoOrderId,
                'success' => $success ? 1 : 0,
                'message' => $message,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->connection->insert(
                $this->resource->getTableName('amwal_webhook_log'),
                $data
            );

            return $this->connection->lastInsertId();
        } catch (\Exception $e) {
            $this->_logger->error('Failed to log webhook: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify signature using private key
     *
     * @param string $payload Raw payload
     * @param string $signature Base64 encoded signature
     * @param string $privateKey PEM format private key
     * @return bool
     */
    public function verifySignature($payload, $signature, $privateKey)
    {
        try {
            // Skip verification if any parameters are missing
            if (empty($payload) || empty($signature) || empty($privateKey)) {
                return false;
            }

            // Decode the base64 signature
            $signatureBytes = $this->base64Json->unserialize($signature);

            // Extract public key from private key
            $privateKeyResource = openssl_pkey_get_private($privateKey);
            if (!$privateKeyResource) {
                $this->_logger->error('Failed to load private key: ' . openssl_error_string());
                return false;
            }

            // Get key details to extract public key components
            $keyDetails = openssl_pkey_get_details($privateKeyResource);
            if (!$keyDetails || !isset($keyDetails['key'])) {
                $this->_logger->error('Failed to extract public key details from private key');
                return false;
            }

            // Use the extracted public key for verification
            $publicKeyResource = openssl_pkey_get_public($keyDetails['key']);
            if (!$publicKeyResource) {
                $this->_logger->error('Failed to load extracted public key: ' . openssl_error_string());
                return false;
            }

            // Verify using PSS padding
            $result = openssl_verify(
                $payload,
                $signatureBytes,
                $publicKeyResource,
                OPENSSL_ALGO_SHA256,
                [
                    'digest_alg' => 'sha256',
                    'padding' => OPENSSL_PKCS1_PSS_PADDING,
                    'mgf1_hash' => 'sha256'
                ]
            );

            return $result === 1;
        } catch (\Exception $e) {
            $this->_logger->error('Signature verification exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Checks if the event type is supported
     *
     * @param string $eventType
     * @return bool
     */
    public function isEventSupported($eventType)
    {
        $supportedEvents = $this->getWebhookEvents();
        return isset($supportedEvents[$eventType]);
    }

    /**
     * Get event display name
     *
     * @param string $eventType
     * @return string
     */
    public function getEventDisplayName($eventType)
    {
        $supportedEvents = $this->getWebhookEvents();
        return $supportedEvents[$eventType] ?? $eventType;
    }
}
