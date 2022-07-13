<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Request\Iframe;

use Amwal\Payments\Lib\Iframe\ApRequestKeys;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Amwal\Payments\Gateway\Subject\Iframe\SubjectReader;
use Magento\Payment\Helper\Formatter;

/**
 * Class LevelTwoDataBuilder
 */
class LevelTwoDataBuilder implements BuilderInterface
{
	use Formatter;

	/**
	 * @var SubjectReader
	 */
	private $subjectReader;

	/**
	 * Constructor
	 *
	 * @param SubjectReader $subjectReader
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
		$orderDO = $paymentDO->getOrder();
		$order = $paymentDO->getPayment()->getOrder();


		return [
			ApRequestKeys::AMOUNT_TAX => $this->formatPrice($order->getTaxAmount()),
			ApRequestKeys::INVOICE_ID => $orderDO->getOrderIncrementId()
		];
	}
}
