<?php

namespace Amwal\Payments\Model\Refund;

use Amwal\Payments\Model\Checkout\AmwalCheckoutAction;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\ErrorReporter;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\OrderRepositoryInterface;
use Amwal\Payments\Model\AmwalClientFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class Refund extends AmwalCheckoutAction
{

    private RefundHandler $refundHandler;

    private Request $restRequest;

    private AmwalClientFactory $amwalClientFactory;

    private OrderRepositoryInterface $orderRepository;

    private Json $jsonSerializer;

    /**
     * @param AmwalClientFactory $amwalClientFactory
     * @param ErrorReporter $errorReporter
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        AmwalClientFactory       $amwalClientFactory,
        ErrorReporter            $errorReporter,
        Config                   $config,
        LoggerInterface          $logger,
        RefundHandler            $refundHandler,
        Request                  $restRequest,
        OrderRepositoryInterface $orderRepository,
        Json                     $jsonSerializer
    )
    {
        parent::__construct($errorReporter, $config, $logger);
        $this->refundHandler = $refundHandler;
        $this->restRequest = $restRequest;
        $this->orderRepository = $orderRepository;
        $this->amwalClientFactory = $amwalClientFactory;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        try {
            $requestBody = $this->restRequest->getBodyParams();
            $orderId = $requestBody['order_id'];
            $refundReason = $requestBody['refund_reason'];
            $refundAmount = (float)$requestBody['refund_amount'];
            $refundShippingAmount = (float)$requestBody['refund_shipping_amount'];
            $refundItems = $requestBody['refund_items'];
            $order = $this->orderRepository->get($orderId);
            $requestBody = [
                'refund_amount' => $refundAmount,
                'metadata' => ['reason' => !empty($refundReason) ? $refundReason : 'Refund request from Magento by Amwal Payments'],
                'transactions_id' => $order->getAmwalOrderId()
            ];
            $refundSuccessful = $this->refundRequest($order, $requestBody);
            if ($refundSuccessful) {
                $creditMemoId = $this->refundHandler->initiateCreditMemo($order, $refundItems, $refundAmount, $refundShippingAmount);
                if ($creditMemoId) {
                    return;
                } else {
                    $message = sprintf(
                        'Unable to initiate credit memo for order with ID "%s".',
                        $orderId
                    );
                    $this->reportError($orderId, $message);
                    $this->logger->error($message);
                    return;
                }
            } else {
                $message = sprintf(
                    'Unable to initiate refund request for order with ID "%s".',
                    $orderId
                );
                $this->reportError($orderId, $message);
                $this->logger->error($message);
                return;
            }
        } catch (\Throwable $e) {
            $message = sprintf(
                'Unable to initiate refund for order with ID "%s". Exception: %s',
                $orderId,
                $e->getMessage()
            );
            $this->reportError($orderId, $message);
            $this->logger->error($message);
            return;
        }
    }

    /**
     * Initiates a refund request to the Amwal API.
     *
     * @param \Magento\Sales\Model\Order $order The order object for which to initiate the refund.
     * @param array $requestBody The request body containing refund details.
     * @return bool Whether the refund request was successful.
     */
    public function refundRequest($order, $requestBody)
    {
        $transactionId = $order->getAmwalOrderId();
        $headers = ['Authorization' => $this->config->getSecretKey()];
        $amwalClient = $this->amwalClientFactory->create();
        try {
            $response = $amwalClient->post(
                'transactions/refund/' . $transactionId . '/',
                [
                    RequestOptions::JSON => $requestBody,
                    RequestOptions::HEADERS => $headers
                ]
            );
            if ($response->getStatusCode() === 200) {
                $responseBody = $this->jsonSerializer->unserialize($response->getBody());
                if ($responseBody['status'] === 'success') {
                    return true;
                }
            }
        } catch (GuzzleException $e) {
            $message = sprintf(
                'Unable to initiate refund request for transaction with ID "%s". Exception: %s',
                $transactionId,
                $e->getMessage()
            );
            $this->reportError($transactionId, $message);
            $this->logger->error($message);
            return false;
        }
    }
}
