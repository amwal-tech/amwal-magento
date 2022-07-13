<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Request\Iframe;

use Amwal\Payments\Lib\Iframe\ApRequestKeys;
use Amwal\Payments\Gateway\Subject\Iframe\SubjectReader;
use Amwal\Payments\Observer\Iframe\DataAssignObserver;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;

/**
 * Payment Data Builder
 */
class PaymentDataBuilder implements BuilderInterface
{
	use Formatter;

	const DOCUMENT_TYPE = 'PPD';
	const IFR_PAYMENT_TYPE = 'IFR';
	const CREDIT_PAYMENT_TYPE = 'CREDIT';

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
		$docType = $this->getDocType($payment->getAdditionalInformation(
				DataAssignObserver::PAYMENT_TYPE
			));
		$result = [
			ApRequestKeys::AMOUNT => $this->formatPrice($orderDO->getGrandTotalAmount()),
			ApRequestKeys::PAYMENT_TOKEN => $payment->getAdditionalInformation(
				DataAssignObserver::PAYMENT_TOKEN
			),
			ApRequestKeys::ORDER_ID => $orderDO->getOrderIncrementId()
		];

		if (!empty($docType)) {
			$result[ApRequestKeys::DOCUMENT_TYPE] = $docType;
		}

		return $result;
	}

	private function getDocType(string $paymentType) {
		if ($paymentType == self::IFR_PAYMENT_TYPE) {
			return self::DOCUMENT_TYPE;
		}
		return null;
	}
}
