<?php
declare(strict_types=1);

namespace Amwal\Payments\Controller\Adminhtml\Webhook;

use Amwal\Payments\Model\AmwalClientFactory;
use Amwal\Payments\Model\Config;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
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
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class ValidateKey extends Action implements HttpPostActionInterface
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
            $scope = (string) $this->getRequest()->getParam('scope', ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
            $scopeId = (int) $this->getRequest()->getParam('scope_id', 0);
            $publicKey = $this->resolvePublicKey();

            $rsaKey = $this->validateAndLoadRsaKey($publicKey);
            $formattedPem = $rsaKey->toString('PKCS8');
            $localFingerprint = hash('sha256', $formattedPem);

            $this->saveInitialKeyConfig($formattedPem, $localFingerprint, $scope, $scopeId);

            $apiDetails = $this->fetchWebhookDetailsFromAmwalApi($formattedPem, $scope, $scopeId);
            $report = $this->buildValidationReport($apiDetails, $localFingerprint);

            $this->cleanConfigCache();

            return $result->setData(array_merge([
                'success' => true,
                'public_key_stored' => true,
                'formatted_public_key' => $formattedPem,
                'merchant_name' => $apiDetails['merchant']['name'] ?? null,
                'api_synced' => ($apiDetails !== null)
            ], $report));
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Resolve public key from request or existing configuration
     *
     * @return string
     * @throws LocalizedException
     */
    private function resolvePublicKey(): string
    {
        $publicKey = trim((string) $this->getRequest()->getParam('public_key'));
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

        return $publicKey;
    }

    /**
     * Store initial public key and local fallback fingerprint
     *
     * @param string $publicKey
     * @param string $localFingerprint
     * @param string $scope
     * @param int $scopeId
     * @return void
     */
    private function saveInitialKeyConfig(string $publicKey, string $localFingerprint, string $scope, int $scopeId): void
    {
        $this->configWriter->save(
            Config::XML_CONFIG_PATH_WEBHOOK_PUBLIC_KEY,
            $this->encryptor->encrypt($publicKey),
            $scope,
            $scopeId
        );

        $this->configWriter->save(
            Config::XML_CONFIG_PATH_WEBHOOK_API_KEY_FINGERPRINT,
            $localFingerprint,
            $scope,
            $scopeId
        );
    }

    /**
     * Build validation report and warnings from Amwal API data
     *
     * @param array|null $apiDetails
     * @param string $localFingerprint
     * @return array
     */
    private function buildValidationReport(?array $apiDetails, string $localFingerprint): array
    {
        $warnings = [];
        $officialFingerprint = $apiDetails['api_key']['key_fingerprint'] ?? $localFingerprint;
        if (empty($officialFingerprint)) {
            $warnings[] = (string) __('No key fingerprint was returned by the Amwal API.');
        }

        $isKeyActive = $this->validateApiKeyStatus($apiDetails['api_key'] ?? null, $warnings);
        $endpointInfo = $this->validateEndpoint($apiDetails['webhook_endpoint'] ?? null, $warnings);
        $eventsInfo = $this->validateEvents($apiDetails['webhook_events'] ?? null, $warnings);

        $keyType = strtolower((string) ($apiDetails['api_key']['type'] ?? ''));
        $storeEnv = $this->detectStoreEnvironment();
        $envMatches = null;
        if (!empty($keyType)) {
            $envMatches = ($keyType === $storeEnv);
            if (!$envMatches) {
                $warnings[] = (string) __(
                    'Environment mismatch: Store is configured for "%1" mode, but this Webhook Key is for "%2". Webhook verification will fail unless environments match.',
                    ucfirst($storeEnv),
                    ucfirst($keyType)
                );
            }
        }

        $message = __('RSA Public Key validated and saved successfully.');
        if ($apiDetails !== null) {
            $message = !empty($warnings)
                ? __('Webhook synchronized with warnings. Please review the details below.')
                : __('Webhook synchronized with Amwal successfully! Events, endpoint URL, and key are verified.');
        }

        return [
            'fingerprint' => $officialFingerprint,
            'key_type' => $keyType,
            'store_env' => $storeEnv,
            'env_matches' => $envMatches,
            'endpoint_url' => $endpointInfo['endpoint_url'],
            'expected_endpoint_url' => $endpointInfo['expected_url'],
            'url_matches' => $endpointInfo['url_matches'],
            'endpoint_active' => $endpointInfo['is_active'],
            'key_active' => $isKeyActive,
            'events' => $eventsInfo['events'],
            'has_order_success' => $eventsInfo['has_order_success'],
            'warnings' => $warnings,
            'message' => $message
        ];
    }

    /**
     * Validate API key active status
     *
     * @param array|null $apiKeyData
     * @param array $warnings
     * @return bool
     */
    private function validateApiKeyStatus(?array $apiKeyData, array &$warnings): bool
    {
        $isActive = $apiKeyData['is_active'] ?? true;
        if ($apiKeyData !== null && !$isActive) {
            $warnings[] = (string) __('The API key is marked as inactive in the Amwal Merchant Portal.');
        }
        return $isActive;
    }

    /**
     * Validate endpoint URL and active status
     *
     * @param array|null $endpointData
     * @param array $warnings
     * @return array
     */
    private function validateEndpoint(?array $endpointData, array &$warnings): array
    {
        $store = $this->storeManager->getStore();
        $expectedUrl = rtrim($store->getBaseUrl(), '/') . '/rest/V1/amwal/webhook';
        $endpointUrl = $endpointData['url'] ?? null;
        $isActive = $endpointData['is_active'] ?? true;
        $urlMatches = null;

        if ($endpointData !== null) {
            if (empty($endpointUrl)) {
                $warnings[] = (string) __('No webhook URL is configured in the Amwal Merchant Portal.');
            } else {
                $urlMatches = (strtolower(rtrim($endpointUrl, '/')) === strtolower(rtrim($expectedUrl, '/')));
                if (!$urlMatches) {
                    $warnings[] = (string) __(
                        'Webhook URL mismatch: configured on Amwal as "%1", but your store endpoint is "%2".',
                        $endpointUrl,
                        $expectedUrl
                    );
                }
                if (strpos($endpointUrl, 'http://') === 0) {
                    $warnings[] = (string) __('Webhook URL uses insecure HTTP. Amwal requires a secure HTTPS endpoint with a valid SSL certificate in production.');
                }
            }

            if (!$isActive) {
                $warnings[] = (string) __('The webhook endpoint is disabled (inactive) in the Amwal Merchant Portal.');
            }
        }

        return [
            'endpoint_url' => $endpointUrl,
            'expected_url' => $expectedUrl,
            'url_matches' => $urlMatches,
            'is_active' => $isActive
        ];
    }

    /**
     * Validate webhook events
     *
     * @param array|null $events
     * @param array $warnings
     * @return array
     */
    private function validateEvents(?array $events, array &$warnings): array
    {
        $eventList = is_array($events) ? $events : [];
        $hasOrderSuccess = in_array('order.success', $eventList, true);

        if ($events !== null) {
            if (empty($eventList)) {
                $warnings[] = (string) __('No webhook events are selected in the Amwal Merchant Portal checklist.');
            } elseif (!$hasOrderSuccess) {
                $warnings[] = (string) __('Critical event "order.success" is not selected in the Merchant Portal. Orders will not be automatically confirmed upon payment.');
            }
        }

        return [
            'events' => $eventList,
            'has_order_success' => $hasOrderSuccess
        ];
    }

    /**
     * Clear config cache
     *
     * @return void
     */
    private function cleanConfigCache(): void
    {
        $this->_objectManager->get(\Magento\Framework\App\Cache\TypeListInterface::class)
            ->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
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
        $clean = trim($publicKey);
        $clean = str_replace(["\r\n", '\r\n', '\n', "\r"], "\n", $clean);
        if (strpos($clean, '-----BEGIN') === false) {
            $clean = "-----BEGIN PUBLIC KEY-----\n" . chunk_split($clean, 64, "\n") . "-----END PUBLIC KEY-----\n";
        }

        try {
            $key = PublicKeyLoader::load($clean);
            if (!($key instanceof PublicKey)) {
                throw new LocalizedException(__('The provided key is not a valid RSA Public Key.'));
            }
            return $key;
        } catch (LocalizedException $e) {
            throw $e;
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
            throw new LocalizedException(
                __('Secret Key is required (Authorization header). Please configure your Secret Key in Amwal API Settings.')
            );
        }

        $merchantId = $this->config->getMerchantId();
        if (empty($merchantId)) {
            throw new LocalizedException(
                __('Merchant ID is required (X-Amwal-Key header). Please configure your Merchant ID in Amwal API Settings.')
            );
        }

        $decryptedSecret = $this->encryptor->decrypt($secretKey);
        $amwalClient = $this->amwalClientFactory->create();

        try {
            $response = $amwalClient->post(
                'api/webhooks/public-key/',
                [
                    RequestOptions::JSON => ['public_key' => $publicKey],
                    RequestOptions::HEADERS => [
                        'Authorization' => $decryptedSecret,
                        'X-Amwal-Key'   => $merchantId,
                        'Content-Type'  => 'application/json',
                    ],
                ]
            );

            $data = $this->json->unserialize($response->getBody()->getContents());
            if (empty($data) || !is_array($data)) {
                return null;
            }

            $this->saveSyncedApiData($data, $scope, $scopeId);
            return $data;
        } catch (ClientException $e) {
            $errorMsg = $this->extractClientErrorMessage($e);
            throw new LocalizedException(__('Failed to sync with Amwal API: %1', $errorMsg));
        } catch (\Exception $e) {
            $this->logger->error('Error syncing webhook details from Amwal API: ' . $e->getMessage());
            throw new LocalizedException(__('Unable to communicate with Amwal API: %1', $e->getMessage()));
        }
    }

    /**
     * Save synced API key fingerprint, type, and webhook events
     *
     * @param array $data
     * @param string $scope
     * @param int $scopeId
     * @return void
     */
    private function saveSyncedApiData(array $data, string $scope, int $scopeId): void
    {
        $officialFingerprint = $data['api_key']['key_fingerprint'] ?? null;
        if ($officialFingerprint) {
            $this->configWriter->save(
                Config::XML_CONFIG_PATH_WEBHOOK_API_KEY_FINGERPRINT,
                $officialFingerprint,
                $scope,
                $scopeId
            );
        }

        $keyType = $data['api_key']['type'] ?? null;
        if ($keyType) {
            $this->configWriter->save(
                Config::XML_CONFIG_PATH_WEBHOOK_KEY_TYPE,
                (string) $keyType,
                $scope,
                $scopeId
            );
        }

        $webhookEvents = $data['webhook_events'] ?? [];
        if (!empty($webhookEvents) && is_array($webhookEvents)) {
            $this->configWriter->save(
                Config::XML_CONFIG_PATH_WEBHOOK_EVENTS,
                implode(',', $webhookEvents),
                $scope,
                $scopeId
            );
        }
    }

    /**
     * Extract descriptive error message from ClientException response
     *
     * @param ClientException $e
     * @return string
     */
    private function extractClientErrorMessage(ClientException $e): string
    {
        $responseBody = $e->getResponse() ? (string) $e->getResponse()->getBody() : '';
        $this->logger->error('[Amwal Webhook Sync] API error response (HTTP ' . $e->getCode() . '): ' . $responseBody);

        try {
            $errorData = $this->json->unserialize($responseBody);
            if (is_array($errorData)) {
                if (isset($errorData['code']) && $errorData['code'] === 'MISSING_AMWAL_KEY') {
                    return (string) ($errorData['error'] ?? __('X-Amwal-Key header is required. Please check your Merchant ID in API Settings.'));
                }
                if (isset($errorData['code']) && $errorData['code'] === 'WEBHOOK_NOT_FOUND') {
                    return (string) ($errorData['detail'] ?? __('No webhook configuration found on Amwal for this Public Key. Please ensure you generated this key in the Amwal Merchant Portal and saved your Webhook URL.'));
                }
                if (isset($errorData['error']) && is_string($errorData['error'])) {
                    return (string) $errorData['error'];
                }
                if (isset($errorData['detail']) && is_string($errorData['detail'])) {
                    return (string) $errorData['detail'];
                }
                if (isset($errorData['message']) && is_string($errorData['message'])) {
                    return (string) $errorData['message'];
                }
                $messages = [];
                foreach ($errorData as $field => $val) {
                    if (is_array($val)) {
                        $messages[] = $field . ': ' . implode(', ', array_map('strval', $val));
                    } elseif (is_scalar($val) && $field !== 'code') {
                        $messages[] = $field . ': ' . $val;
                    }
                }
                if (!empty($messages)) {
                    return implode('; ', $messages);
                }
            }
        } catch (\Exception $je) {
            // Ignore JSON decode error
        }

        if (!empty($responseBody)) {
            return substr(strip_tags($responseBody), 0, 300);
        }

        return 'Amwal API returned HTTP ' . $e->getCode();
    }

    /**
     * Detect store environment mode (sandbox or production)
     *
     * @return string
     */
    private function detectStoreEnvironment(): string
    {
        $merchantId = strtolower($this->config->getMerchantId());
        return (strpos($merchantId, 'sandbox') !== false) ? 'sandbox' : 'production';
    }
}
