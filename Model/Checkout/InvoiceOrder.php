<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Checkout;

use Amwal\Payments\Model\Config;
use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
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

class InvoiceOrder
{
    private InvoiceRepositoryInterface $invoiceRepository;
    private ScopeConfigInterface $scopeConfig;
    private InvoiceSender $invoiceSender;
    private BuilderInterface $transactionBuilder;
    private Config $config;
    private OrderRepositoryInterface $orderRepository;
    private LoggerInterface $logger;

    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        ScopeConfigInterface $scopeConfig,
        InvoiceSender $invoiceSender,
        BuilderInterface $transactionBuilder,
        Config $config,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->scopeConfig = $scopeConfig;
        $this->invoiceSender = $invoiceSender;
        $this->transactionBuilder = $transactionBuilder;
        $this->config = $config;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
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

        $paymentObj = $order->getPayment();

        if (!$paymentObj) {
            $this->logger->error(sprintf(
                'Unable to find payment for order with ID %s',
                $order->getId()
            ));
            throw new LocalizedException(__('Invoice cannot be created because no payment can be found for the order'));
        }

        $paymentObj->setTransactionId($amwalOrderData->getId());
        $paymentObj->setAmountAuthorized($order->getTotalDue());
        $paymentObj->setBaseAmountAuthorized($order->getBaseTotalDue());

        // Prepare transaction
        $transaction = $this->transactionBuilder->setPayment($paymentObj)
            ->setOrder($order)
            ->setTransactionId($amwalOrderData->getId())
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
