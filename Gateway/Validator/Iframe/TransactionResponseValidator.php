<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Validator\Iframe;

use Amwal\Payments\Gateway\Subject\Iframe\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Validates the status of an attempted transaction
 */
class TransactionResponseValidator extends AbstractValidator
{
	const BP_SUCCESS_RESPONSE = 'APPROVED';
	const BP_ERROR_RESPONSE = "ERROR";
	const BP_DECLINED_RESPONSE = "DECLINED";
	const BP_MISSING_RESPONSE = "MISSING";
	
	private $apSuccessResponses = [
		self::BP_SUCCESS_RESPONSE
	];

	private $apFailureResponses = [
		self::BP_ERROR_RESPONSE, 
		self::BP_DECLINED_RESPONSE, 
		self::BP_MISSING_RESPONSE
	];

	/**
	 * @var SubjectReader
	 */
	private $subjectReader;

	/**
	 * @param ResultInterfaceFactory $resultFactory
	 * @param SubjectReader $subjectReader
	 */
	public function __construct(ResultInterfaceFactory $resultFactory, SubjectReader $subjectReader)
	{
		parent::__construct($resultFactory);
		$this->subjectReader = $subjectReader;
	}

	/**
	 * @inheritdoc
	 */
	public function validate(array $validationSubject): ResultInterface
	{
		$apResponse = $this->subjectReader->readApResponseFromResponse($validationSubject);

		if (!$this->isSuccessful($apResponse)) {
			$errorMessages = [];
			$errorCodes = [];
			array_push($errorMessages, $apResponse->getMessage());
			array_push($errorCodes, $apResponse->getStatus());

			return $this->createResult(false, $errorMessages, $errorCodes);
		}

		return $this->createResult(true);
	}


	private function isSuccessful(\Amwal\Payments\Lib\Iframe\ApResponse $apResponse) 
	{
		$status = $apResponse->getStatus();
		return (
			in_array($status, $this->apSuccessResponses) && 
			!in_array($status, $this->apFailureResponses)
		);
	}
}
