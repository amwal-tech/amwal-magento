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
     * Executes the refund request.
     *
     * @return mixed[] The response body.
     */
    public function execute()
    {
        $requestBody = $this->restRequest->getBodyParams();
        $orderId = $requestBody['order_id'];
        $refundReason = $requestBody['refund_reason'];
        $refundAmount = (float)$requestBody['refund_amount'];
        $refundShippingAmount = (float)$requestBody['refund_shipping_amount'];
        $refundItems = $requestBody['refund_items'];
        $order = $this->orderRepository->get($orderId);
        $requestBody = [
            'refund_amount' => $refundAmount,
            'metadata' => ['reason' => !empty($refundReason) ? $refundReason : __('Refund request from Magento by Amwal Payments')],
            'transactions_id' => $order->getAmwalOrderId()
        ];
        $refundSuccessful = $this->refundRequest($order, $requestBody);
        if ($refundSuccessful['data']['status']) {
            $creditMemo = $this->refundHandler->initiateCreditMemo($order, $refundItems, $refundAmount, $refundShippingAmount);
            if ($creditMemo) {
                return [
                    'data' => $this->jsonResponse([
                        'status' => true,
                        'message' => __('The refund was successful.')
                    ])
                ];
            }
        }
        return $refundSuccessful;
    }

    /**
     * Initiates a refund request to the Amwal API.
     *
     * @param \Magento\Sales\Model\Order $order The order object for which to initiate the refund.
     * @param array $requestBody The request body containing refund details.
     * @return mixed[] The response body.
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
            $responseBody = $this->jsonSerializer->unserialize($response->getBody());

            if ($response->getStatusCode() === 200 && $responseBody['status'] === 'success') {
                return [
                    'data' => $this->jsonResponse([
                        'status' => true,
                        'message' => __('The refund was successful.')
                    ])
                ];
            }
            if ($response->getStatusCode() === 400) {

                return [
                    'data' => $this->jsonResponse([
                        'status' => false,
                        'message' => $responseBody['message']
                    ])
                ];
            }
        } catch (GuzzleException $e) {
            $message = sprintf(
                'Unable to initiate refund request for transaction with ID "%s". Exception: %s',
                $transactionId,
                $e->getMessage()
            );
            $this->reportError($transactionId, $message);
            $this->logger->error($message);
            return [
                'data' => $this->jsonResponse([
                    'status' => false,
                    'message' => __('Something went wrong while refunding the order. Please try again later.')
                ])
            ];
        }
    }


    protected function jsonResponse($data)
    {
        return $data;
    }
}
