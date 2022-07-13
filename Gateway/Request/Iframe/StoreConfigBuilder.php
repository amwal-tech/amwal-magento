<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Request\Iframe;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Amwal\Payments\Gateway\Subject\Iframe\SubjectReader;

class StoreConfigBuilder implements BuilderInterface
{
	/**
	 * @var SubjectReader
	 */
	private $subjectReader;

	/**
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
		$order = $paymentDO->getOrder();

		return [
			'store_id' => $order->getStoreId()
		];
	}
}
