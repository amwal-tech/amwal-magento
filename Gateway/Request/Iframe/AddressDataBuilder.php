<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Request\Iframe;

use Amwal\Payments\Lib\Iframe\ApRequestKeys;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Amwal\Payments\Gateway\Subject\Iframe\SubjectReader;

/**
 * Class AddressDataBuilder
 */
class AddressDataBuilder implements BuilderInterface
{
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

		$order = $paymentDO->getOrder();
		$billingAddress = $order->getBillingAddress();
		
		return [
			ApRequestKeys::ADDRESS_1 => $billingAddress->getStreetLine1(),
			ApRequestKeys::ADDRESS_2 => $billingAddress->getStreetLine2(),
			ApRequestKeys::LOCALITY => $billingAddress->getCity(),
			ApRequestKeys::REGION => $billingAddress->getRegionCode(),
			ApRequestKeys::POSTAL_CODE => $billingAddress->getPostcode(),
			ApRequestKeys::COUNTRY => $billingAddress->getCountryId()
		];
	}
}
