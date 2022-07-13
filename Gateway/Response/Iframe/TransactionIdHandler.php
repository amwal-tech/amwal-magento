<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Response\Iframe;

use Amwal\Payments\Gateway\Subject\Iframe\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class TransactionIdHandler
 */
class TransactionIdHandler implements HandlerInterface
{
	const TRANS_ID = 'iframe_transaction_id';
	const VOID_ID = 'iframe_void_transaction_id';
	const REFUND_ID = 'iframe_refund_transaction_id';
	const CAPTURE_ID = 'iframe_capture_transaction_id';

	/**
	 * @var SubjectReader
	 */
	private $subjectReader;

	/**
	 * Constructor
	 *
	 * @param SubjectReader $subjectReader
	 */
	public function __construct(
		SubjectReader $subjectReader
	) {
		$this->subjectReader = $subjectReader;
	}

	/**
	 * @inheritdoc
	 */
	public function handle(array $handlingSubject, array $response)
	{
		$paymentDO = $this->subjectReader->readPayment($handlingSubject);
		$payment = $paymentDO->getPayment();

		/** @var \Amwal\Payments\Lib\Iframe\ApResponse $apResponse */
		$apResponse = $this->subjectReader->readApResponse($response);
		
		if ($payment instanceof Payment) {
			$this->setTransactionId($payment, $apResponse);
		}

		$closeP = $this->shouldCloseParentTransaction($payment);
		$payment->setShouldCloseParentTransaction($closeP);
		$payment->setIsTransactionClosed($this->shouldCloseTransaction());
	}

	/**
	 * Sets payment transaction Id for non-refund, non-void transactions
	 * sets additional information for refund/void transactions
	 *
	 * @param Payment $payment
	 * @param \Amwal\Payments\Lib\Iframe\ApResponse $apResponse
	 * @return void
	 */
	protected function setTransactionId(Payment $payment, \Amwal\Payments\Lib\Iframe\ApResponse $apResponse) 
	{
		$transId = $apResponse->getTransId();
		$payment->setTransactionId($transId);
		$payment->setTransactionAdditionalInfo(
			self::TRANS_ID,
			$transId
		);
	}


	/**
	 * Whether transaction should be closed
	 *
	 * @return bool
	 */
	protected function shouldCloseTransaction()
	{
		return false;
	}

	/**
	 * Whether parent transaction should be closed
	 *
	 * @param Payment $orderPayment
	 * @return bool
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	protected function shouldCloseParentTransaction(Payment $orderPayment)
	{
		return false;
	}
}
