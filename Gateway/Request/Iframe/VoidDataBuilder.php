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
 * Void Data Builder
 */
class VoidDataBuilder implements BuilderInterface
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

		return [
			ApRequestKeys::PAYMENT_TOKEN => $payment->getParentTransactionId()
				?: $payment->getLastTransId()
		];
	}
}
