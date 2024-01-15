<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Checkout;

use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\ErrorReporter;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class InvoiceOrder extends AmwalCheckoutAction
{
    private $invoiceRepository;
    private $scopeConfig;
    private $invoiceSender;
    private $transactionBuilder;
    private $orderRepository;
    private $checkoutSession;
    private $messageManager;

    /**
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param InvoiceSender $invoiceSender
     * @param BuilderInterface $transactionBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param CheckoutSession $checkoutSession
     * @param ManagerInterface $messageManager
     * @param ErrorReporter $errorReporter
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        ScopeConfigInterface $scopeConfig,
        InvoiceSender $invoiceSender,
        BuilderInterface $transactionBuilder,
        OrderRepositoryInterface $orderRepository,
        CheckoutSession $checkoutSession,
        ManagerInterface $messageManager,
        ErrorReporter $errorReporter,
        Config $config,
        LoggerInterface $logger
    ) {
        parent::__construct($errorReporter, $config, $logger);
        $this->invoiceRepository = $invoiceRepository;
        $this->scopeConfig = $scopeConfig;
        $this->invoiceSender = $invoiceSender;
        $this->transactionBuilder = $transactionBuilder;
        $this->orderRepository = $orderRepository;
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
    }

    /**
     * @param OrderInterface $order
     * @param DataObject $amwalOrderData
     * @return void
     * @throws LocalizedException
     */
    public function execute(OrderInterface $order, DataObject $amwalOrderData): void
    {
        if ($order->getState() === Order::STATE_PAYMENT_REVIEW) {
            $order->setState(Order::STATE_NEW);
        }
        $amwalOrderId = $amwalOrderData->getId();
        $paymentObj = $order->getPayment();

        if (!$paymentObj) {
            $message = sprintf('Unable to find payment for order with ID %s', $order->getId());
            $this->reportError($amwalOrderId, $message);
            $this->logger->error($message);
            throw new LocalizedException(__('Invoice cannot be created because no payment can be found for the order'));
        }

        $paymentObj->setTransactionId($amwalOrderId);
        $paymentObj->setAmountAuthorized($order->getTotalDue());
        $paymentObj->setBaseAmountAuthorized($order->getBaseTotalDue());

        // Prepare transaction
        $transaction = $this->transactionBuilder->setPayment($paymentObj)
            ->setOrder($order)
            ->setTransactionId($amwalOrderId)
            ->build(TransactionInterface::TYPE_AUTH);

        $transaction->setIsClosed(false);
        try {
            $transaction->save();
        } catch (Exception $e) {
            $this->logger->error(sprintf(
                'Unable to save the transaction for order with ID: %s. Exception %s',
                $order->getId(),
                $e->getMessage()
            ));
            throw new Exception($e->getMessage());
        }

        $this->createInvoice($order, $amwalOrderData);

        $status = $this->config->getOrderConfirmedStatus();
        $order->setState($status);
        $order->setStatus($status);

        $order->addCommentToStatusHistory(
            __('Successfully completed Amwal payment with transaction ID %1', $amwalOrderData->getId())
        );

        $this->orderRepository->save($order);
    }


    /**
     * @param OrderInterface $order
     * @param DataObject $amwalOrderData
     * @throws LocalizedException
     */
    public function createInvoice(OrderInterface $order, DataObject $amwalOrderData): void
    {
        if ($order->canInvoice()) {
            try {
                $invoice = $order->prepareInvoice();
                $invoice->getOrder()->setIsInProcess(true);

                // set transaction id so you can do an online refund from credit memo
                $invoice->setTransactionId($amwalOrderData->getId());
                $invoice->register()->pay();

                $this->invoiceRepository->save($invoice);
            } catch (Exception $e) {
                $this->logger->error(sprintf(
                    'Unable to invoice the order with ID %s. Exception: %s',
                    $order->getId(),
                    $e->getMessage()
                ));
                throw new LocalizedException(__('Something went wrong while invoicing the order.'));
            }

            $invoiceAutoMail = $this->scopeConfig->isSetFlag(
                InvoiceIdentity::XML_PATH_EMAIL_ENABLED,
                ScopeInterface::SCOPE_STORE,
                $order->getStoreId()
            );

            if ($invoiceAutoMail) {
                $this->invoiceSender->send($invoice);
            }
        } else {
            $this->logger->error(sprintf(
                'Order with ID %s cannot be invoiced',
                $order->getId()
            ));
            throw new LocalizedException(__('The order cannot be invoiced.'));
        }
    }
}
