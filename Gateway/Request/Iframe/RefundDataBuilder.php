<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Request\Iframe;

use \Amwal\Payments\Lib\Iframe\ApRequestKeys;
use \Amwal\Payments\Gateway\Subject\Iframe\SubjectReader;
use \Amwal\Payments\Observer\Iframe\DataAssignObserver;
use \Magento\Payment\Gateway\Request\BuilderInterface;
use \Magento\Payment\Helper\Formatter;
use \Amwal\Payments\Gateway\Response\Iframe\TransactionIdHandler;

/**
 * Refund Data Builder
 */
class RefundDataBuilder implements BuilderInterface
{
	use Formatter;

	/**
	 * @var SubjectReader
	 */
	private $subjectReader;

	/**
	 * @param SubjectReader $subjectReader
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(SubjectReader $subjectReader)
	{
		$this->subjectReader = $subjectReader;
	}

	/**
	 * @inheritdoc
	 */
	public function build(array $buildSubject)
	{
		$paymentDO = $this->subjectReader->readPayment($buildSubject);

		/** @var Payment $payment */
		$payment = $paymentDO->getPayment();

		$amount = null;
		try {
			$amount = $this->formatPrice($this->subjectReader->readAmount($buildSubject));
		} catch (\InvalidArgumentException $e) {
			// pass
		}

		$result = [];
		$paymentToken = $payment->getParentTransactionId() ?: $payment->getLastTransId();
		$captureToRefund = $payment->getAuthorizationTransaction()->getAdditionalInformation('iframe_capture_transaction_id');
		if (!empty($captureToRefund)) {
			$paymentToken = $captureToRefund;
		}
		$result[ApRequestKeys::PAYMENT_TOKEN] = $paymentToken;
		$result[ApRequestKeys::AMOUNT] = $amount;

		return $result;
	}
}
