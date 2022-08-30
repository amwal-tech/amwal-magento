<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Request\Iframe;

use Amwal\Payments\Lib\Iframe\ApRequestKeys;
use Amwal\Payments\Gateway\Config\Iframe\Config;
use Amwal\Payments\Gateway\Subject\Iframe\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Adds Merchant Account ID to the request.
 */
class MerchantAccountDataBuilder implements BuilderInterface
{

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var SubjectReader
	 */
	private $subjectReader;

	/**
	 * Constructor
	 *
	 * @param Config $config
	 * @param SubjectReader $subjectReader
	 */
	public function __construct(Config $config, SubjectReader $subjectReader)
	{
		$this->config = $config;
		$this->subjectReader = $subjectReader;
	}

	/**
	 * @inheritdoc
	 */
	public function build(array $buildSubject)
	{
		$paymentDO = $this->subjectReader->readPayment($buildSubject);
		$orderDO = $paymentDO->getOrder();

		$merchantAccountId = $this->config->getMerchantId($orderDO->getStoreId());

		return [ ApRequestKeys::MERCHANT_ID => $merchantAccountId ];
	}
}
