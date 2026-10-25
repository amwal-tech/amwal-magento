<?php
declare(strict_types=1);

namespace Amwal\Payments\Controller\Adminhtml\Webhook;

use Amwal\Payments\Model\AmwalClientFactory;
use Amwal\Payments\Model\Config;
use GuzzleHttp\RequestOptions;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA\PublicKey;
use Psr\Log\LoggerInterface;

/**
 * Controller to validate Webhook Public Key, sync with Amwal API, and store configuration
 */
class ValidateKey extends Action
{
    public const ADMIN_RESOURCE = 'Amwal_Payments::config';

    protected JsonFactory $resultJsonFactory;
    protected Json $json;
    protected WriterInterface $configWriter;
    protected EncryptorInterface $encryptor;
    protected Config $config;
    protected AmwalClientFactory $amwalClientFactory;
    protected StoreManagerInterface $storeManager;
    protected LoggerInterface $logger;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Json $json
     * @param WriterInterface $configWriter
     * @param EncryptorInterface $encryptor
     * @param Config $config
     * @param AmwalClientFactory $amwalClientFactory
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Json $json,
        WriterInterface $configWriter,
        EncryptorInterface $encryptor,
        Config $config,
        AmwalClientFactory $amwalClientFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->json = $json;
        $this->configWriter = $configWriter;
        $this->encryptor = $encryptor;
        $this->config = $config;
        $this->amwalClientFactory = $amwalClientFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Execute key validation, Amwal API sync, and storage
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $publicKey = trim((string) $this->getRequest()->getParam('public_key'));
            $scope = (string) $this->getRequest()->getParam('scope', ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
            $scopeId = (int) $this->getRequest()->getParam('scope_id', 0);

            // If empty in request, check if we already have a saved key in this scope
            if (empty($publicKey)) {
                $savedKey = $this->config->getWebhookPublicKey();
                if (!empty($savedKey)) {
                    $publicKey = trim($savedKey);
                }
            }

            if (empty($publicKey)) {
                throw new LocalizedException(
                    __('Please paste your RSA Public Key (PEM format) from the Amwal Merchant Portal into the Webhook Public Key field.')
                );
            }

            // Clean & validate RSA Public Key format
            $rsaKey = $this->validateAndLoadRsaKey($publicKey);

            // Default local fingerprint from key
            $localFingerprint = hash('sha256', $rsaKey->toString('PKCS8'));

            // Store encrypted public key
            $this->configWriter->save(
                Config::XML_CONFIG_PATH_WEBHOOK_PUBLIC_KEY,
                $this->encryptor->encrypt($publicKey),
                $scope,
                $scopeId
            );

            // Store computed fallback fingerprint
            $this->configWriter->save(
                Config::XML_CONFIG_PATH_WEBHOOK_API_KEY_FINGERPRINT,
                $localFingerprint,
                $scope,
                $scopeId
            );

            // Query Amwal API for webhook details, events & official fingerprint
            $apiDetails = $this->fetchWebhookDetailsFromAmwalApi($publicKey, $scope, $scopeId);

            $warnings = [];

            // 1. Validate API Key status
            $isKeyActive = $apiDetails['api_key']['is_active'] ?? true;
            if ($apiDetails && !$isKeyActive) {
                $warnings[] = (string) __('The API key is marked as inactive in the Amwal Merchant Portal.');
            }

            // 2. Validate Key Fingerprint
            $officialFingerprint = $apiDetails['api_key']['key_fingerprint'] ?? $localFingerprint;
            if (empty($officialFingerprint)) {
                $warnings[] = (string) __('No key fingerprint was returned by the Amwal API.');
            }

            // 3. Validate Endpoint URL & Active status
            $endpointData = $apiDetails['webhook_endpoint'] ?? [];
            $endpointUrl = $endpointData['url'] ?? null;
            $isEndpointActive = $endpointData['is_active'] ?? true;

            $store = $this->storeManager->getStore();
            $expectedWebhookUrl = rtrim($store->getBaseUrl(), '/') . '/rest/V1/amwal/webhook';
            $urlMatches = null;

            if ($apiDetails) {
                if (empty($endpointUrl)) {
                    $warnings[] = (string) __('No webhook URL is configured in the Amwal Merchant Portal.');
                } else {
                    $normalizedConfigured = strtolower(rtrim($endpointUrl, '/'));
                    $normalizedExpected = strtolower(rtrim($expectedWebhookUrl, '/'));
                    $urlMatches = ($normalizedConfigured === $normalizedExpected);

                    if (!$urlMatches) {
                        $warnings[] = (string) __(
                            'Webhook URL mismatch: configured on Amwal as "%1", but your store endpoint is "%2".',
                            $endpointUrl,
                            $expectedWebhookUrl
                        );
                    }

                    if (strpos($endpointUrl, 'http://') === 0) {
                        $warnings[] = (string) __('Webhook URL uses insecure HTTP. Amwal requires a secure HTTPS endpoint with a valid SSL certificate in production.');
                    }
                }

                if (!$isEndpointActive) {
                    $warnings[] = (string) __('The webhook endpoint is disabled (inactive) in the Amwal Merchant Portal.');
                }
            }

            // 4. Validate Webhook Events
            $events = $apiDetails['webhook_events'] ?? [];
            $hasEvents = !empty($events);
            $hasOrderSuccess = in_array('order.success', $events, true);

            if ($apiDetails) {
                if (!$hasEvents) {
                    $warnings[] = (string) __('No webhook events are selected in the Amwal Merchant Portal checklist.');
                } elseif (!$hasOrderSuccess) {
                    $warnings[] = (string) __('Critical event "order.success" is not selected in the Merchant Portal. Orders will not be automatically confirmed upon payment.');
                }
            }

            // Clear config cache so changes take effect immediately
            $this->_objectManager->get(\Magento\Framework\App\Cache\TypeListInterface::class)
                ->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);

            $message = __('RSA Public Key validated and saved successfully.');
            if ($apiDetails) {
                if (!empty($warnings)) {
                    $message = __('Webhook synchronized with warnings. Please review the details below.');
                } else {
                    $message = __('Webhook synchronized with Amwal successfully! Events, endpoint URL, and key are verified.');
                }
            }

            return $result->setData([
                'success' => true,
                'fingerprint' => $officialFingerprint,
                'public_key_stored' => true,
                'endpoint_url' => $endpointUrl,
                'expected_endpoint_url' => $expectedWebhookUrl,
                'url_matches' => $urlMatches,
                'endpoint_active' => $isEndpointActive,
                'key_active' => $isKeyActive,
                'events' => $events,
                'has_order_success' => $hasOrderSuccess,
                'merchant_name' => $apiDetails['merchant']['name'] ?? null,
                'api_synced' => ($apiDetails !== null),
                'warnings' => $warnings,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Validate and load RSA Public Key
     *
     * @param string $publicKey
     * @return PublicKey
     * @throws LocalizedException
     */
    private function validateAndLoadRsaKey(string $publicKey): PublicKey
    {
        if (strpos($publicKey, '-----BEGIN') === false || strpos($publicKey, '-----END') === false) {
            throw new LocalizedException(
                __('Invalid key format. Key must be in PEM format starting with "-----BEGIN PUBLIC KEY-----".')
            );
        }

        try {
            $key = PublicKeyLoader::load($publicKey);
            if (!($key instanceof PublicKey)) {
                throw new LocalizedException(__('The provided key is not a valid RSA Public Key.'));
            }
            return $key;
        } catch (\Exception $e) {
            throw new LocalizedException(__('Failed to load RSA Public Key: %1', $e->getMessage()));
        }
    }

    /**
     * Query Amwal API for webhook details, events, and key validation.
     *
     * @param string $publicKey
     * @param string $scope
     * @param int $scopeId
     * @return array|null
     * @throws LocalizedException
     */
    private function fetchWebhookDetailsFromAmwalApi(string $publicKey, string $scope, int $scopeId): ?array
    {
        $secretKey = $this->config->getSecretKey();
        if (empty($secretKey)) {
            $this->logger->info('[Amwal Webhook Sync] Secret key is empty, skipping API sync.');
            return null;
        }

        $decryptedSecret = $this->encryptor->decrypt($secretKey);
        $amwalClient = $this->amwalClientFactory->create();

        try {
            $response = $amwalClient->post(
                'api/webhooks/public-key/',
                [
                    RequestOptions::JSON => [
                        'public_key' => $publicKey,
                    ],
                    RequestOptions::HEADERS => [
                        'Authorization' => $decryptedSecret,
                        'Content-Type'  => 'application/json',
                    ],
                ]
            );

            $contents = $response->getBody()->getContents();
            $data = $this->json->unserialize($contents);

            if (empty($data) || !is_array($data)) {
                return null;
            }

            // Save official Amwal fingerprint if available
            $officialFingerprint = $data['api_key']['key_fingerprint'] ?? null;
            if ($officialFingerprint) {
                $this->configWriter->save(
                    Config::XML_CONFIG_PATH_WEBHOOK_API_KEY_FINGERPRINT,
                    $officialFingerprint,
                    $scope,
                    $scopeId
                );
            }

            // Save and sync webhook events returned by Amwal API
            $webhookEvents = $data['webhook_events'] ?? [];
            if (!empty($webhookEvents) && is_array($webhookEvents)) {
                $this->configWriter->save(
                    Config::XML_CONFIG_PATH_WEBHOOK_EVENTS,
                    implode(',', $webhookEvents),
                    $scope,
                    $scopeId
                );
            }

            return $data;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseBody = $e->getResponse() ? (string) $e->getResponse()->getBody() : '';
            $errorMsg = 'Amwal API returned HTTP ' . $e->getCode();
            try {
                $errorData = $this->json->unserialize($responseBody);
                if (isset($errorData['message'])) {
                    $errorMsg = (string) $errorData['message'];
                } elseif (isset($errorData['detail'])) {
                    $errorMsg = (string) $errorData['detail'];
                }
            } catch (\Exception $je) {
                // Ignore JSON unserialize error
            }
            throw new LocalizedException(__('Failed to sync with Amwal API: %1', $errorMsg));
        } catch (\Exception $e) {
            $this->logger->error('Error syncing webhook details from Amwal API: ' . $e->getMessage());
            throw new LocalizedException(__('Unable to communicate with Amwal API: %1', $e->getMessage()));
        }
    }
}
