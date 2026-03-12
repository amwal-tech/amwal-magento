<?php

namespace Amwal\Payments\Model;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Url\DecoderInterface;

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
     * @var DecoderInterface
     */
    protected $decoder;

    /**
     * @param Context $context
     * @param ResourceConnection $resource
     * @param Json $json
     * @param DecoderInterface $decoder
     */
    public function __construct(
        Context $context,
        ResourceConnection $resource,
        Json $json,
        DecoderInterface $decoder
    ) {
        parent::__construct($context);
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->json = $json;
        $this->decoder = $decoder;
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
     * Verify signature using public key
     *
     * @param string $payload Raw payload
     * @param string $signature Base64 encoded signature
     * @param string $publicKey PEM format public key
     * @return bool
     */
    public function verifySignature($payload, $signature, $publicKey)
    {
        try {
            // Skip verification if any parameters are missing
            if (empty($payload) || empty($signature) || empty($publicKey)) {
                return false;
            }

            // Decode the base64 signature using Magento's decoder
            $signatureBytes = $this->decoder->decode($signature);
            if ($signatureBytes === false || empty($signatureBytes)) {
                $this->_logger->error('Failed to base64 decode signature');
                return false;
            }

            // Load public key directly
            $publicKeyResource = openssl_pkey_get_public($publicKey);
            if (!$publicKeyResource) {
                $this->_logger->error('Failed to load public key: ' . openssl_error_string());
                return false;
            }

            // Verify using PSS padding
            $result = openssl_verify(
                $payload,
                $signatureBytes,
                $publicKeyResource,
                OPENSSL_ALGO_SHA256
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
