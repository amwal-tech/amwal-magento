<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Response\Iframe;

use Amwal\Payments\Gateway\Subject\Iframe\SubjectReader;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Class IfrDetailsHandler
 */
class IfrDetailsHandler implements HandlerInterface
{
	const IFR_PAYMENT_TYPE = 'IFR';
	const CARD_PAYMENT_TYPE = 'CREDIT';
	const ACCOUNT_NUMBER = "account_number";
	const ACCOUNT_TYPE = "account_type";
	const ROUTING_NUMBER = "routing_number";
	const BIN = "ifr_bin";
	const SAVINGS_CODE = "S";
	const CHECKING_CODE = "C";
	const SAVINGS = "savings";
	const CHECKING = "checking";
	const ACCOUNT_PATTERN = "/\A([C|S]):(\d*):X*(\d{4})\z/";

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
		$apResponse = $this->subjectReader->readApResponse($response);
		$pType = $apResponse->getPaymentType();
		if ($pType == self::IFR_PAYMENT_TYPE) 
		{
			$paymentDO = $this->subjectReader->readPayment($handlingSubject);
			$payment = $paymentDO->getPayment();
			ContextHelper::assertOrderPayment($payment);

			$maskedAccount = $apResponse->getMaskedAccount();
			$acctInfo = $this->parseAccountString($maskedAccount);

			$payment->setEcheckAccountType($acctInfo[self::ACCOUNT_TYPE]);
			$payment->setEcheckRoutingNumber($acctInfo[self::ROUTING_NUMBER]);
			$payment->setEcheckAccountName($maskedAccount);
			$payment->setAdditionalInformation(self::BIN, $acctInfo[self::BIN]);
		}
	}

	private function parseAccountString($acctString)
	{
		$_r = array();
		preg_match(self::ACCOUNT_PATTERN, $acctString, $_r);
		
		if (count($_r) < 4)
		{
			throw new \InvalidArgumentException('Unable to parse IFR account string from Iframe.');
		}
		
		return [
			self::ACCOUNT_TYPE => $this->mapAccountType($_r[1]),
			self::ROUTING_NUMBER => $_r[2],
			self::BIN => $_r[3]
		];
	}

	private function mapAccountType($acctType) {
		if (strtoupper($acctType) == self::SAVINGS_CODE)
		{
			return self::SAVINGS;
		}
		if (strtoupper($acctType) == self::CHECKING_CODE)
		{
			return self::CHECKING;
		}

		throw new \InvalidArgumentException('Unable to identify Iframe IFR account type.');
	}
}
