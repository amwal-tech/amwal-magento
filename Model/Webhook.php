<?php
namespace Amwal\Payments\Model;

use Amwal\Payments\Api\WebHookInterface;
use Amwal\Payments\Api\Data\WebhookResponseInterface;
use Amwal\Payments\Model\Data\WebhookResponse;
use Amwal\Payments\Model\Event\HandlerFactory;
use Amwal\Payments\Model\Config;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;

/**
 * Webhook processor implementation
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Webhook implements WebHookInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var WebhookHelper
     */
    private $webhookHelper;

    /**
     * @var HandlerFactory
     */
    private $eventHandlerFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param RequestInterface $request
     * @param Json $json
     * @param LoggerInterface $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param WebhookHelper $webhookHelper
     * @param HandlerFactory $eventHandlerFactory
     * @param Config $config
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        RequestInterface $request,
        Json $json,
        LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        WebhookHelper $webhookHelper,
        HandlerFactory $eventHandlerFactory,
        Config $config,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->request = $request;
        $this->json = $json;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->webhookHelper = $webhookHelper;
        $this->eventHandlerFactory = $eventHandlerFactory;
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * Process incoming webhook data
     *
     * @return WebhookResponseInterface
     */
    public function execute()
    {
        /** @var WebhookResponseInterface $response */
        $response = $this->objectManager->create(WebhookResponse::class);

        try {
            $payload = $this->getPayload();
            $data = $this->parsePayload($payload);
            $this->logWebhookReceived($data);
            $this->validateWebhook($payload, $data);
            $this->processWebhookEvent($data);

            $response->setSuccess(true);
            $response->setMessage('Webhook processed successfully');
        } catch (\Exception $e) {
            $this->logger->error('Amwal webhook error: ' . $e->getMessage());
            $response->setSuccess(false);
            $response->setMessage($e->getMessage());
        }

        return $response;
    }

    /**
     * Get the raw payload from the request
     *
     * @return string
     * @throws LocalizedException
     */
    private function getPayload(): string
    {
        $payload = $this->request->getContent();

        if (empty($payload)) {
            throw new LocalizedException(__('Empty payload received'));
        }

        return $payload;
    }

    /**
     * Parse the JSON payload
     *
     * @param string $payload
     * @return array
     */
    private function parsePayload(string $payload): array
    {
        return $this->json->unserialize($payload);
    }

    /**
     * Log webhook data for debugging
     *
     * @param array $data
     * @return void
     */
    private function logWebhookReceived(array $data): void
    {
        $this->logger->info('Amwal webhook received: ' . $this->json->serialize($data));
        $this->logger->info('X-Signature: ' . $this->request->getHeader('X-Signature'));
        $this->logger->info('X-API-Key: ' . $this->request->getHeader('X-API-Key'));
    }

    /**
     * Validate webhook authenticity
     *
     * @param string $payload Raw payload
     * @param array $data Parsed webhook data
     * @return bool
     * @throws LocalizedException
     */
    private function validateWebhook(string $payload, array $data)
    {
        // Check if webhooks are enabled
        if (!$this->config->isWebhookEnabled()) {
            throw new LocalizedException(__('Webhooks are not enabled'));
        }

        $signature = $this->request->getHeader('X-Signature');
        $eventType = $data['event_type'] ?? 'unknown';
        $orderId = $data['id'] ?? null;
        $apiKeyFingerprint = $this->request->getHeader('X-API-Key') ?: 'missing';

        // Get private key for verification
        $privateKey = $this->config->getWebhookPrivateKey();
        if (!$privateKey) {
            throw new LocalizedException(__('Missing webhook private key configuration'));
        }

        // Verify signature
        $signatureVerified = $this->webhookHelper->verifySignature($payload, $signature, $privateKey);

        // Validate API key fingerprint
        $this->validateApiKeyFingerprint($apiKeyFingerprint, $data, $payload);

        // Log the webhook validation result
        $logId = $this->webhookHelper->logWebhook(
            $eventType,
            $payload,
            $apiKeyFingerprint,
            $signatureVerified,
            $orderId,
            null,
            $signatureVerified,
            $signatureVerified ? 'Signature verified' : 'Invalid or missing signature'
        );

        if (!$signatureVerified) {
            $this->logger->warning('Webhook signature verification failed or missing. Log ID: ' . $logId);
        }

        return true;
    }

    /**
     * Validate API key fingerprint
     *
     * @param string $apiKeyFingerprint
     * @param array $data
     * @param string $payload
     * @throws LocalizedException
     * @return void
     */
    private function validateApiKeyFingerprint(string $apiKeyFingerprint, array $data, string $payload): void
    {
        if (!$apiKeyFingerprint) {
            throw new LocalizedException(__('Missing API key fingerprint'));
        }

        $configApiKeyFingerprint = $this->config->getApiKeyFingerprint();

        // Validate if configured
        if ($configApiKeyFingerprint && $apiKeyFingerprint !== $configApiKeyFingerprint) {
            $this->logValidationFailure(
                $data['event_type'] ?? 'unknown',
                $payload,
                $apiKeyFingerprint,
                $data['order_id'] ?? null,
                'API key fingerprint mismatch'
            );

            throw new LocalizedException(__('Invalid API key fingerprint'));
        }
    }

    /**
     * Log webhook validation failure
     *
     * @param string $eventType
     * @param string $payload
     * @param string $apiKeyFingerprint
     * @param string|null $orderId
     * @param string $errorMessage
     * @return string Log ID
     */
    private function logValidationFailure(
        string $eventType,
        string $payload,
        string $apiKeyFingerprint,
        ?string $orderId,
        string $errorMessage
    ): string {
        return $this->webhookHelper->logWebhook(
            $eventType,
            $payload,
            $apiKeyFingerprint,
            false,
            $orderId,
            null,
            false,
            $errorMessage
        );
    }

    /**
     * Process webhook event based on type
     *
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    private function processWebhookEvent(array $data): void
    {
        // Get event type from payload
        $eventType = $data['event_type'] ?? null;

        if (!$eventType) {
            throw new LocalizedException(__('Missing event type in webhook payload'));
        }

        $this->logger->info('Processing Amwal webhook event: ' . $eventType);

        // Check if event type is supported
        $supportedEvents = $this->webhookHelper->getWebhookEvents();
        if (!isset($supportedEvents[$eventType])) {
            $this->logger->warning('Unhandled Amwal webhook event type: ' . $eventType);
            return;
        }

        // Execute event handler
        $this->executeEventHandler($eventType, $data);
    }

    /**
     * Execute the appropriate event handler
     *
     * @param string $eventType
     * @param array $data
     * @throws \Exception
     * @return void
     */
    private function executeEventHandler(string $eventType, array $data): void
    {
        try {
            $this->logger->info("Executing event handler for $eventType");

            $handler = $this->eventHandlerFactory->create($eventType);
            $this->logger->debug("Handler created successfully", ['handler_class' => get_class($handler)]);

            $order = $this->getOrderFromWebhook($data);
            $this->logger->debug("Order retrieval attempt completed", ['order_exists' => (bool)$order]);

            if ($order) {
                $this->logger->info("Processing event $eventType for order #{$order->getIncrementId()}");
                $this->logger->debug("Order details", [
                    'order_id' => $order->getIncrementId(),
                    'order_status' => $order->getStatus()
                ]);

                $handler->execute($order, $data);
                $this->logger->debug("Handler execution completed successfully");
            } else {
                $this->logger->warning("Order not found for event: $eventType");
                $this->logger->debug("Order lookup failed", ['webhook_data' => $this->json->serialize($data)]);
            }
        } catch (\Exception $e) {
            $this->logger->debug("Exception details", [
                'exception_class' => get_class($e),
                'exception_code' => $e->getCode(),
                'exception_trace' => $e->getTraceAsString()
            ]);
            $this->logger->error("Error processing event $eventType: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find the order by reference ID from webhook
     *
     * @param array $data
     * @return OrderInterface|null
     */
    private function getOrderFromWebhook(array $data): ?OrderInterface
    {
        $this->logger->info('Finding order by webhook data');

        // Extract order ID or reference from webhook payload
        $amwalOrderId = $data['data']['id'] ?? null;
        $reference = $data['data']['ref_id'] ?? null;

        if (!$amwalOrderId && !$reference) {
            $this->logger->error('Missing order reference in webhook payload');
            return null;
        }

        // Try finding by direct Amwal order ID
        if ($amwalOrderId) {
            $order = $this->findOrderByAmwalId($amwalOrderId);
            if ($order) {
                return $order;
            }
        }

        $this->logger->error("Could not find order with provided identifiers");
        return null;
    }

    /**
     * Find order by Amwal order ID with error handling
     *
     * @param string $amwalOrderId
     * @return OrderInterface|null
     */
    public function findOrderByAmwalId(string $amwalOrderId): ?OrderInterface
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('amwal_order_id', $amwalOrderId)
                ->create();

            $orders = $this->orderRepository->getList($searchCriteria)->getItems();
            $order = reset($orders);

            if ($order) {
                $this->logger->info("Found order #{$order->getIncrementId()} using amwal_order_id: $amwalOrderId");
                return $order;
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to find order by ID $amwalOrderId: " . $e->getMessage());
        }

        return null;
    }
}
