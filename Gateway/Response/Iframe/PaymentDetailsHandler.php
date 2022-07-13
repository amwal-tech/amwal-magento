<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Response\Iframe;

use Amwal\Payments\Iframe\Observer\DataAssignObserver;
use Amwal\Payments\Gateway\Subject\Iframe\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Class PaymentDetailsHandler
 */
class PaymentDetailsHandler implements HandlerInterface
{
	const STATUS = 'iframe_status';
	const PAYMENT_TYPE = 'payment_type';
	const MESSAGE = 'iframe_message';
	const AVS_RESPONSE = 'avs_response';
	const CVV2_RESPONSE = 'cvv_response';
	const AUTH_CODE = 'processor_auth_code';
	const MASTER_ID = 'iframe_master_transaction_id';
	const RESPONSE_ARRAY = 'iframe_raw_response';
	const CARD_PAYMENT_TYPE = 'CREDIT';


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
		$paymentDO = $this->subjectReader->readPayment($handlingSubject);
		$payment = $paymentDO->getPayment();

		/** @var \Amwal\Payments\Lib\Iframe\ApResponse $apResponse */
		$apResponse = $this->subjectReader->readApResponse($response);

		$transId = $apResponse->getTransId();
		$payment->setLastTransId($transId);
		
		$pType = $apResponse->getPaymentType();
		if ($pType == self::CARD_PAYMENT_TYPE) 
		{
			$payment->setCcTransId($transId);
		} 

		$masterId = $apResponse->getMasterId();
		if ($masterId != null && $payment->getParentTransactionId() == null)
		{
			$payment->setParentTransactionId($masterId);
		}
		
		$payment->setTransactionAdditionalInfo(
			self::STATUS,
			$apResponse->getStatus()
		);

		$payment->setTransactionAdditionalInfo(
			self::PAYMENT_TYPE,
			$apResponse->getPaymentType()
		);

		$payment->setTransactionAdditionalInfo(
			self::MESSAGE,
			$apResponse->getMessage()
		);

		$payment->setTransactionAdditionalInfo(
			self::AVS_RESPONSE,
			$apResponse->getAVSResponse()
		);

		$payment->setTransactionAdditionalInfo(
			self::CVV2_RESPONSE,
			$apResponse->getCVV2Response()
		);

		$payment->setTransactionAdditionalInfo(
			self::AUTH_CODE,
			$apResponse->getAuthCode()
		);

		$payment->setTransactionAdditionalInfo(
			self::MASTER_ID,
			$apResponse->getMasterId()
		);

		$payment->setTransactionAdditionalInfo(
			self::RESPONSE_ARRAY,
			$apResponse->getResponse()
		);
	}
}
