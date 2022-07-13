<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Request\Iframe;

use Amwal\Payments\Lib\Iframe\ApRequestKeys;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Amwal\Payments\Gateway\Subject\Iframe\SubjectReader;
use Magento\Framework\App\ObjectManager;

/**
 * Class CustomerDataBuilder
 */
class CustomerDataBuilder implements BuilderInterface
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

		$orderDO = $paymentDO->getOrder();
		$billingAddress = $orderDO->getBillingAddress();

		return [
			ApRequestKeys::FIRST_NAME => $billingAddress->getFirstname(),
			ApRequestKeys::LAST_NAME => $billingAddress->getLastname(),
			ApRequestKeys::COMPANY => $billingAddress->getCompany(),
			ApRequestKeys::PHONE => $billingAddress->getTelephone(),
			ApRequestKeys::EMAIL => $billingAddress->getEmail(),
			ApRequestKeys::IP_ADDRESS => $orderDO->getRemoteIp()
		];
	}
}
