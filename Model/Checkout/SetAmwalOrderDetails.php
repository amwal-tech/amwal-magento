<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Checkout;

use Amwal\Payments\Model\AmwalClientFactory;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\ErrorReporter;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * Add order details in the Amwal system after order placement
 */
class SetAmwalOrderDetails extends AmwalCheckoutAction
{

    private AmwalClientFactory $amwalClientFactory;

    /**
     * @param AmwalClientFactory $amwalClientFactory
     * @param ErrorReporter $errorReporter
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        AmwalClientFactory $amwalClientFactory,
        ErrorReporter $errorReporter,
        Config $config,
        LoggerInterface $logger
    ) {
        parent::__construct($errorReporter, $config, $logger);
        $this->amwalClientFactory = $amwalClientFactory;
    }

    /**
     * @param OrderInterface $order
     * @param string $amwalOrderId
     * @param string $triggerContext
     * @return void
     */
    public function execute(OrderInterface $order, string $amwalOrderId, string $triggerContext): void
    {
        $orderDetails = [];
        $orderDetails['order_id'] = $order->getIncrementId();
        $orderDetails['order_entity_id'] = $order->getEntityId();
        $orderDetails['order_created_at'] = $order->getCreatedAt();
        $orderDetails['order_content'] = json_encode($this->getOrderContent($order));
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
            $message = sprintf(
                'Unable to set Order details in Amwal for order with ID "%s". Exception: %s',
                $amwalOrderId,
                $e->getMessage()
            );
            $this->reportError($amwalOrderId, $message);
            $this->logger->error($message);
            return;
        }

        $responseStatusCode = $response->getStatusCode();
        if ($responseStatusCode !== 200) {
            $message = sprintf(
                'Unable to set Order details in Amwal for order with ID "%s". Received status: %s - %s',
                $amwalOrderId,
                $responseStatusCode,
                $response->getReasonPhrase()
            );
            $this->reportError($amwalOrderId, $message);
            $this->logger->error($message);
        }
    }

    /**
     * @param $order
     * @return array
     */
    private function getOrderContent($order): array
    {
        $items = $order->getAllItems();
        $orderContent = [];
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $orderContent[] = [
                'id' => $item->getProductId(),
                'name' => $item->getName(),
                'quantity' => $item->getQtyOrdered(),
                'total' => $item->getRowTotalInclTax()
            ];
        }
        return $orderContent;
    }
}
