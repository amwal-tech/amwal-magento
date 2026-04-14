<?php

declare(strict_types=1);

namespace Amwal\Payments\Model;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;

/**
 * Common helpers for Amwal webhook processing and signature verification.
 */
class WebhookHelper
{
    /**
     * The exact fields Amwal includes in the signed payload, derived from
     * the reference test script (x-signature.php). The live webhook uses
     * "id" for what the signature calls "transaction_id".
     */
    private const SIGNED_FIELDS = [
        'amount',
        'client_first_name',
        'client_last_name',
        'payment_link_id',
        'payment_option',
        'status',
        'transaction_id',   // sourced from data['id'] in the live webhook
    ];

    private const WEBHOOK_EVENTS = [
        'order.created' => 'Order Created',
        'order.success' => 'Order Success',
        'order.failed'  => 'Order Failed',
        'order.updated' => 'Order Updated',
    ];

    private ResourceConnection $resource;
    private Json $json;
    private LoggerInterface $webhookLogger;
    private AdapterInterface $connection;

    public function __construct(
        ResourceConnection $resource,
        Json $json,
        LoggerInterface $webhookLogger
    ) {
        $this->resource       = $resource;
        $this->json           = $json;
        $this->webhookLogger  = $webhookLogger;
        $this->connection     = $resource->getConnection();
    }

    // -------------------------------------------------------------------------
    // Event registry
    // -------------------------------------------------------------------------

    /** @return array<string, string> */
    public function getWebhookEvents(): array
    {
        return self::WEBHOOK_EVENTS;
    }

    public function isEventSupported(string $eventType): bool
    {
        return isset(self::WEBHOOK_EVENTS[$eventType]);
    }

    public function getEventDisplayName(string $eventType): string
    {
        return self::WEBHOOK_EVENTS[$eventType] ?? $eventType;
    }

    // -------------------------------------------------------------------------
    // Logging
    // -------------------------------------------------------------------------

    /**
     * Persist a webhook event to amwal_webhook_log.
     *
     * @param array|string $payload
     * @return int|false  Inserted row ID, or false on failure.
     */
    public function logWebhook(
        string $eventType,
               $payload,
        ?string $apiKeyFingerprint = null,
        bool $signatureVerified = false,
        ?string $orderId = null,
        ?string $magentoOrderId = null,
        bool $success = false,
        ?string $message = null
    ) {
        try {
            if (is_array($payload)) {
                $payload = $this->json->serialize($payload);
            }

            $this->connection->insert(
                $this->resource->getTableName('amwal_webhook_log'),
                [
                    'event_type'          => $eventType,
                    'payload'             => $payload,
                    'api_key_fingerprint' => $apiKeyFingerprint,
                    'signature_verified'  => (int) $signatureVerified,
                    'order_id'            => $orderId,
                    'magento_order_id'    => $magentoOrderId,
                    'success'             => (int) $success,
                    'message'             => $message,
                    'created_at'          => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                ]
            );

            return (int) $this->connection->lastInsertId();
        } catch (\Exception $e) {
            $this->webhookLogger->error('Failed to log webhook: ' . $e->getMessage());
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Signature verification
    // -------------------------------------------------------------------------

    /**
     * Verify an RSA-PSS SHA-256 webhook signature.
     *
     * Amwal signs only the 7 fields in SIGNED_FIELDS, not the full body.
     *
     * Signing process (mirrored from Amwal's Python backend):
     *   1. Build a dict with the 7 fields, mapping data['id'] → 'transaction_id'.
     *   2. Sort keys alphabetically  (sort_keys=True).
     *   3. Serialize with Python's default json.dumps separators (", " / ": ").
     *   4. Sign with RSA-PSS, SHA-256, MGF1-SHA-256, salt = PSS.MAX_LENGTH (222).
     *
     * @param string $payload   Raw JSON from the HTTP body.
     * @param string $signature Base64-encoded X-Signature header value.
     * @param string $publicKey PEM RSA public key.
     */
    public function verifySignature(string $payload, string $signature, string $publicKey): bool
    {
        try {
            if (empty($payload) || empty($signature) || empty($publicKey)) {
                $this->webhookLogger->error('[Amwal] Signature verification skipped: missing parameters', [
                    'has_payload'    => !empty($payload),
                    'has_signature'  => !empty($signature),
                    'has_public_key' => !empty($publicKey),
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

            $candidateStr = $this->buildSignedPayload($payload);
            if ($candidateStr === null) {
                return false;
            }

            $this->webhookLogger->debug('[Amwal] Signed payload candidate', ['value' => $candidateStr]);

            // Try salt=222 (Python PSS.MAX_LENGTH for 2048-bit + SHA-256),
            // then salt=32 (SHA-256 digest length) as a fallback.
            foreach ([222, 32] as $salt) {
                $verified = $rsaPublicKey
                    ->withPadding(RSA::SIGNATURE_PSS)
                    ->withHash('sha256')
                    ->withMGFHash('sha256')
                    ->withSaltLength($salt)
                    ->verify($candidateStr, $signatureBytes);

                if ($verified) {
                    $this->webhookLogger->info("[Amwal] ✓ Signature VALID (salt=$salt)");
                    return true;
                }

                $this->webhookLogger->warning("[Amwal] ✗ Signature failed (salt=$salt)");
            }

            $this->webhookLogger->error('[Amwal] ✗ Signature verification failed', [
                'signed_payload'  => $candidateStr,
                'signature_b64'   => $signature,
                'public_key_head' => substr(trim($publicKey), 0, 60),
            ]);

            return false;

        } catch (\Exception $e) {
            $this->webhookLogger->error(
                '[Amwal] Signature verification exception: ' . $e->getMessage(),
                ['trace' => $e->getTraceAsString()]
            );
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Extract and serialize the 7 signed fields from the raw webhook body.
     *
     * Returns null and logs on JSON decode failure.
     */
    private function buildSignedPayload(string $rawPayload): ?string
    {
        $envelope = json_decode($rawPayload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->webhookLogger->error('[Amwal] Failed to decode payload: ' . json_last_error_msg());
            return null;
        }

        $data = $envelope['data'] ?? [];

        $signed = [
            'amount'            => $data['amount']            ?? null,
            'client_first_name' => $data['client_first_name'] ?? null,
            'client_last_name'  => $data['client_last_name']  ?? null,
            'payment_link_id'   => $data['payment_link_id']   ?? null,
            'payment_option'    => $data['payment_option']    ?? null,
            'status'            => $data['status']            ?? null,
            'transaction_id'    => $data['id']                ?? null,  // 'id' in webhook = 'transaction_id' in signature
        ];

        ksort($signed);  // mirrors Python's sort_keys=True

        return $this->toPythonSeparators(
            json_encode($signed, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Convert a compact PHP JSON string to Python json.dumps default separators.
     *
     * PHP: {"key":"value","key2":"value2"}
     * Python: {"key": "value", "key2": "value2"}
     *
     * str_replace is safe here because the 7 signed fields are known: `amount`
     * is numeric and always comes first (alphabetically), so every `,` in the
     * serialised string is structurally followed by `"` — never inside a value.
     */
    private function toPythonSeparators(string $json): string
    {
        return str_replace(['":', ',"'], ['": ', ', "'], $json);
    }

    /**
     * Base64-decode the incoming signature header.
     *
     * NOTE: Never use Magento's Url\Decoder — it corrupts binary data.
     */
    private function decodeSignature(string $signature): ?string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $bytes = base64_decode($signature, true);
        if ($bytes === false || $bytes === '') {
            $this->webhookLogger->error('[Amwal] base64_decode failed for signature: ' . $signature);
            return null;
        }
        return $bytes;
    }

    /**
     * Load and validate a PEM RSA public key via phpseclib3.
     */
    private function loadRsaPublicKey(string $publicKey): ?\phpseclib3\Crypt\RSA\PublicKey
    {
        $publicKey = trim($publicKey);
        if (strpos($publicKey, '-----BEGIN') === false) {
            $this->webhookLogger->error(
                '[Amwal] Public key missing PEM BEGIN marker. Starts with: ' . substr($publicKey, 0, 40)
            );
            return null;
        }

        $key = PublicKeyLoader::load($publicKey);
        if (!($key instanceof \phpseclib3\Crypt\RSA\PublicKey)) {
            $this->webhookLogger->error('[Amwal] Key is not an RSA PublicKey, got: ' . get_class($key));
            return null;
        }

        return $key;
    }
}
