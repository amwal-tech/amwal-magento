<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Checkout;

use Amwal\Payments\Model\AmwalClientFactory;
use Amwal\Payments\Model\Config;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * Add order details in the Amwal system after order placement
 */
class SetAmwalOrderDetails
{

    private AmwalClientFactory $amwalClientFactory;
    private Config $config;
    private LoggerInterface $logger;

    /**
     * @param AmwalClientFactory $amwalClientFactory
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        AmwalClientFactory $amwalClientFactory,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->amwalClientFactory = $amwalClientFactory;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param OrderInterface $order
     * @param string $amwalOrderId
     * @return void
     */
    public function execute(OrderInterface $order, string $amwalOrderId, string $triggerContext): void
    {
        $orderDetails = [];
        $orderDetails['order_id'] = $order->getIncrementId();
        $orderDetails['order_entity_id'] = $order->getEntityId();
        $orderDetails['order_created_at'] = $order->getCreatedAt();
        $orderDetails['order_position'] = $triggerContext;
        $orderDetails['plugin_type'] = 'magento';
        $orderDetails['plugin_version'] = $this->config->getVersion();

        $amwalClient = $this->amwalClientFactory->create();
        try {
            $response = $amwalClient->post(
                'transactions/' . $amwalOrderId . '/set_order_details',
                [
                    RequestOptions::JSON => $orderDetails
                ]
            );
        } catch (GuzzleException $e) {
            $this->logger->error(sprintf(
                'Unable to set Order details in Amwal for order with ID "%s". Exception: %s',
                $amwalOrderId,
                $e->getMessage()
            ));
            return;
        }

        $responseStatucCode = $response->getStatusCode();
        if ($responseStatucCode !== 200) {
            $this->logger->error(sprintf(
                'Unable to set Order details in Amwal for order with ID "%s". Received status: %s - %s',
                $amwalOrderId,
                $responseStatucCode,
                $response->getReasonPhrase()
            ));
        }
    }
}
