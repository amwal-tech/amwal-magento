<?php

namespace Amwal\Payments\Model;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;

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
     * @var LoggerInterface
     */
    private $webhookLogger;

    /**
     * @param Context $context
     * @param ResourceConnection $resource
     * @param Json $json
     * @param LoggerInterface $webhookLogger
     */
    public function __construct(
        Context $context,
        ResourceConnection $resource,
        Json $json,
        LoggerInterface $webhookLogger
    ) {
        parent::__construct($context);
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->json = $json;
        $this->webhookLogger = $webhookLogger;
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
            $this->webhookLogger->error('Failed to log webhook: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify signature using public key with RSA-PSS (SHA-256)
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
                $this->webhookLogger->error('Signature verification skipped: missing parameters', [
                    'has_payload' => !empty($payload),
                    'has_signature' => !empty($signature),
                    'has_publicKey' => !empty($publicKey),
                ]);
                return false;
            }

            $signatureBytes = $this->decodeSignature($signature);
            if ($signatureBytes === null) {
                return false;
            }

            $rsaPublicKey = $this->loadRsaPublicKey($publicKey);
            if ($rsaPublicKey === null) {
                return false;
            }

            /** @var \phpseclib3\Crypt\RSA\PublicKey $pssPubKey */
            $pssPubKey = $rsaPublicKey->withPadding(RSA::SIGNATURE_PSS)
                ->withHash('sha256')
                ->withMGFHash('sha256');

            $result = $pssPubKey->verify($payload, $signatureBytes);

            if (!$result) {
                $this->webhookLogger->warning('RSA-PSS signature verification returned false');
            }

            return $result;
        } catch (\Exception $e) {
            $this->webhookLogger->error('Signature verification exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Decode a base64-encoded signature string.
     *
     * @param string $signature
     * @return string|null Decoded bytes or null on failure
     */
    private function decodeSignature(string $signature): ?string
    {
        // NOTE: Do NOT use Magento's Url\Decoder here — it runs urldecode/strtr
        // and sessionUrlVar() on the result, which corrupts binary signature data.
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $signatureBytes = base64_decode($signature, true);
        if ($signatureBytes === false || empty($signatureBytes)) {
            $this->webhookLogger->error('Failed to base64 decode signature');
            return null;
        }
        return $signatureBytes;
    }

    /**
     * Load and validate an RSA public key from PEM string.
     *
     * @param string $publicKey PEM format public key
     * @return \phpseclib3\Crypt\RSA\PublicKey|null
     */
    private function loadRsaPublicKey(string $publicKey): ?\phpseclib3\Crypt\RSA\PublicKey
    {
        $publicKey = trim($publicKey);
        if (strpos($publicKey, '-----BEGIN') === false) {
            $this->webhookLogger->error(
                'Public key does not appear to be in PEM format (missing BEGIN marker). Key starts with: '
                . substr($publicKey, 0, 40)
            );
            return null;
        }

        $key = PublicKeyLoader::load($publicKey);
        if (!($key instanceof \phpseclib3\Crypt\RSA\PublicKey)) {
            $this->webhookLogger->error('Loaded key is not an RSA PublicKey, got: ' . get_class($key));
            return null;
        }

        return $key;
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
