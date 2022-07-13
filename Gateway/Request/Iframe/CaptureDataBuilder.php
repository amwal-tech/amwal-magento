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
 * Payment Data Builder
 */
class CaptureDataBuilder implements BuilderInterface
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
		$payment = $paymentDO->getPayment();
		$orderDO = $paymentDO->getOrder();


		$result = [];
		$authTransaction = $payment->getAuthorizationTransaction();
		
		$result[ApRequestKeys::AMOUNT] = $this->formatPrice($orderDO->getGrandTotalAmount());
		$result[ApRequestKeys::PAYMENT_TOKEN] = $authTransaction->getAdditionalInformation(TransactionIdHandler::TRANS_ID);

		return $result;
	}
}
