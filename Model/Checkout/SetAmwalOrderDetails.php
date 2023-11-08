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
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
/**
 * Add order details in the Amwal system after order placement
 */
class SetAmwalOrderDetails extends AmwalCheckoutAction
{

    private AmwalClientFactory $amwalClientFactory;
    private StoreManagerInterface $storeManager;
    private Json $json;

    /**
     * @param AmwalClientFactory $amwalClientFactory
     * @param StoreManagerInterface $storeManager
     * @param ErrorReporter $errorReporter
     * @param Config $config
     * @param LoggerInterface $logger
     * @param Json $json
     */
    public function __construct(
        AmwalClientFactory $amwalClientFactory,
        StoreManagerInterface $storeManager,
        ErrorReporter $errorReporter,
        Config $config,
        LoggerInterface $logger,
        Json $json
    ) {
        parent::__construct($errorReporter, $config, $logger);
        $this->amwalClientFactory = $amwalClientFactory;
        $this->storeManager = $storeManager;
        $this->json = $json;
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
        $orderDetails['order_id'] = $order->getEntityId();
        $orderDetails['order_created_at'] = $order->getCreatedAt();
        $orderDetails['order_content'] = $this->json->serialize($this->getOrderContent($order));
        $orderDetails['order_position'] = $triggerContext;
        $orderDetails['order_url'] = $this->getOrderUrl($order);
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
                'total' => $item->getRowTotalInclTax(),
                'url' => $item->getProduct()->getProductUrl(),
                'image' => $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $item->getProduct()->getImage(),
            ];
        }
        return $orderContent;
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    private function getOrderUrl(OrderInterface $order): string
    {
        return $this->storeManager->getStore()->getBaseUrl() . 'sales/order/view/order_id/' . $order->getEntityId();
    }
}
