<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Response\Iframe;

use Amwal\Payments\Gateway\Config\Iframe\Config;
use Amwal\Payments\Gateway\Subject\Iframe\SubjectReader;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Class CardDetailsHandler
 */
class CardDetailsHandler implements HandlerInterface
{
	const IFR_PAYMENT_TYPE = 'IFR';
	const CARD_PAYMENT_TYPE = 'CREDIT';
	const CARD_NUMBER = "cc_number";

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
	public function __construct(
		Config $config,
		SubjectReader $subjectReader
	) {
		$this->config = $config;
		$this->subjectReader = $subjectReader;
	}

	/**
	 * @inheritdoc
	 */
	public function handle(array $handlingSubject, array $response)
	{
		$apResponse = $this->subjectReader->readApResponse($response);
		$pType = $apResponse->getPaymentType();
		if ($pType == self::CARD_PAYMENT_TYPE) 
		{
			$paymentDO = $this->subjectReader->readPayment($handlingSubject);
			$payment = $paymentDO->getPayment();
			ContextHelper::assertOrderPayment($payment);

			$maskedAccount = $apResponse->getMaskedAccount();
			$bin = str_replace(["X"], "", $maskedAccount);
			$apCardType = $apResponse->getCardType();
			
			$payment->setCcLast4($bin);
			$payment->setCcExpMonth($apResponse->getCcExpireMonth());
			$payment->setCcExpYear($apResponse->getCcExpireYear());
			$payment->setCcType($this->getCreditCardType($apCardType));
			$payment->setAdditionalInformation(self::CARD_NUMBER, $maskedAccount);
			$payment->setAdditionalInformation(OrderPaymentInterface::CC_TYPE, $apCardType);
		}
	}

	/**
	 * Get type of credit card mapped from Iframe
	 *
	 * @param string $type
	 * @return array
	 */
	private function getCreditCardType($type)
	{
		$replaced = str_replace(' ', '-', strtolower($type));
		$mapper = $this->config->getCcTypesMapper();

		return $mapper[strtoupper($replaced)];
	}
}
