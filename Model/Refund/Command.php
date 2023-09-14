<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Refund;

use Amwal\Payments\Model\AmwalClientFactory;
use Amwal\Payments\Model\Config;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class Command implements CommandInterface
{

    private AmwalClientFactory $amwalClientFactory;
    private Config $config;
    private OrderRepositoryInterface $orderRepository;
    private Context $context;
    private LoggerInterface $logger;

    /**
     * @param AmwalClientFactory $amwalClientFactory
     * @param Config $config
     * @param OrderRepositoryInterface $orderRepository
     * @param Context $context
     * @param LoggerInterface $logger
     */
    public function __construct(
        AmwalClientFactory $amwalClientFactory,
        Config $config,
        OrderRepositoryInterface $orderRepository,
        Context $context,
        LoggerInterface $logger
    ) {
        $this->amwalClientFactory = $amwalClientFactory;
        $this->config = $config;
        $this->orderRepository = $orderRepository;
        $this->context = $context;
        $this->logger = $logger;
    }

    /**
     * @param array $commandSubject
     * @return array[]|null
     * @throws LocalizedException
     */
    public function execute(array $commandSubject)
    {
        $this->logger->notice('Starting Refund');

        if (!isset($commandSubject['payment'])) {
            $this->logger->error('Missing payment information for refund command.');
            throw new LocalizedException(__('No payment found to initiate refund command.'));
        }

        if (!isset($commandSubject['amount'])) {
            $this->logger->error('Missing amount for refund command.');
            throw new LocalizedException(__('No amount found to initiate refund command.'));
        }

        /** @var PaymentDataObject $payment */
        $payment = $commandSubject['payment'];
        $orderId = $payment->getOrder()->getId();

        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Unable to load order for refund.'));
        }

        $transactionId = $order->getAmwalOrderId();

        if (!$transactionId) {
            $this->logger->notice('Missing Amwal transaction ID for refund.');
            throw new LocalizedException(__('Amwal Transaction ID can not be found for this order, and is required for refunding.'));
        }

        $this->logger->notice(sprintf('Refunding transaction with ID "%s"', $transactionId));

        $amwalClient = $this->amwalClientFactory->create();

        try {
            $amwalClient->post(
                'transactions/refund/' . $transactionId . '/',
                [
                    RequestOptions::JSON => $this->getRequestBody((float) $commandSubject['amount'], $transactionId),
                    RequestOptions::HEADERS => ['Authorization' => $this->config->getSecretKey()]
                ]
            );
        } catch (GuzzleException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();

            $message = sprintf(
                "Unable to initiate refund request for transaction with ID \"%s\".\n Response: %s",
                $transactionId,
                $responseBody
            );
            $this->logger->error($message);

            throw new LocalizedException(__('Unable to process refund in Amwal, please try again later.'));
        }

        $this->logger->notice(sprintf('Refund for transaction with ID "%s" completed successfully', $transactionId));

        return null;
    }

    /**
     * @param float $amount
     * @param string $transactionId
     * @return array
     */
    private function getRequestBody(float $amount, string $transactionId): array
    {
        return [
            'refund_amount' => $amount,
            'metadata' => ['reason' => $this->getRefundReason()],
            'transactions_id' => $transactionId
        ];
    }

    /**
     * @return Phrase|string
     */
    private function getRefundReason()
    {
        $creditMemoInfo = $this->context->getRequest()->getParam('creditmemo');
        return $creditMemoInfo['comment_text'] ?? __('Refund request from Magento by Amwal Payments');
    }
}
