<?php
/**
 * Amwal Payments webhook API key creation controller
 */
namespace Amwal\Payments\Controller\Adminhtml\Webhook;

use Amwal\Payments\Model\AmwalClientFactory;
use Amwal\Payments\Model\Config;
use GuzzleHttp\RequestOptions;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Controller for creating API key
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateApiKey extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Amwal_Payments::config';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var AmwalClientFactory
     */
    private AmwalClientFactory $amwalClientFactory;

    /**
     * @var Config
     */
    private Config $config;


    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Curl $curl
     * @param Json $json
     * @param WriterInterface $configWriter
     * @param EncryptorInterface $encryptor
     * @param AmwalClientFactory $amwalClientFactory
     * @param Config $config
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Curl $curl,
        Json $json,
        WriterInterface $configWriter,
        EncryptorInterface $encryptor,
        AmwalClientFactory $amwalClientFactory,
        Config $config
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->curl = $curl;
        $this->json = $json;
        $this->configWriter = $configWriter;
        $this->encryptor = $encryptor;
        $this->amwalClientFactory = $amwalClientFactory;
        $this->config = $config;
    }

    /**
     * Execute API key creation
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            // Validate form key is already checked in the parent execute method

            // Get webhook URL from request
            $webhookUrl = $this->getRequest()->getParam('webhook_url');
            // Get webhook events from request
            $webhookEvents = $this->getRequest()->getParam('webhook_events');

            if (empty($webhookUrl)) {
                throw new LocalizedException(__('Webhook URL is required'));
            }
            if (empty($webhookEvents)) {
                throw new LocalizedException(__('Webhook events are required'));
            }
            if (empty($this->config->getSecretKey())) {
                throw new LocalizedException(__('A secret key is required. To retrieve it, navigate to the Merchant Portal and go to Integration > API Keys.'));
            }
            $requestData = [
                'api_key_name' => 'Amwal Magento Webhook',
                'url' => $webhookUrl,
                'description' => 'Amwal Magento Webhook API key',
                "event_type_names" => $webhookEvents,
                'api_key_scopes' => ["trigger_events", "manage_webhooks"]
            ];
            $amwalClient = $this->amwalClientFactory->create();
            $response = $amwalClient->post(
                'api/create-webhook-and-apikey/',
                [
                    RequestOptions::JSON => $requestData,
                    RequestOptions::HEADERS => ['Authorization' => $this->config->getSecretKey(), 'X-API-Key' => $this->config->getSecretKey()]
                ]
            );
            $responseData = $this->json->unserialize($response->getBody()->getContents());

            // Check for errors
            if (!isset($responseData['webhook']) || !isset($responseData['api_key'])) {
                $error = isset($responseData['error']) ? $responseData['error'] : 'Unknown error';
                throw new LocalizedException(__('Failed to create Webhook API key: %1', $error));
            }
            $webhookData = $responseData['webhook'];
            $apiKeyData = $responseData['api_key'];

            // Save configuration
            $scope = $this->getRequest()->getParam('scope', 'default');
            $scopeId = (int) $this->getRequest()->getParam('scope_id', 0);

            // Save API key fingerprint
            $this->configWriter->save(
                'payment/amwal_payments/webhook/api_key_fingerprint',
                $apiKeyData['fingerprint'],
                $scope,
                $scopeId
            );

            // Save webhook events
            $this->configWriter->save(
                'payment/amwal_payments/webhook/events',
                implode(',', $webhookEvents),
                $scope,
                $scopeId
            );

            // Save private key (encrypted)
            if (isset($apiKeyData['private_key'])) {
                $encryptedPrivateKey = $this->encryptor->encrypt($apiKeyData['private_key']);
                $this->configWriter->save(
                    'payment/amwal_payments/webhook/private_key',
                    $encryptedPrivateKey,
                    $scope,
                    $scopeId
                );
            }

            // Clear the config cache
            $this->_objectManager->get(\Magento\Framework\App\Cache\TypeListInterface::class)
                ->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);

            // Return success response
            return $result->setData([
                'success' => true,
                'webhook_id' => $webhookData['id'],
                'key_fingerprint' => $apiKeyData['fingerprint'] ?? null,
                'private_key' => $apiKeyData['private_key'] ?? null,
                'message' => __('Webhook API key created successfully')
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
