<?php
namespace Amwal\Payments\Lib\Iframe;

use Amwal\Payments\Lib\Iframe\ApRequestKeys;

class ApResponse
{
	private $status;
	private $message;
	private $transId;
	private $maskedAccount;
	private $ccExpireMonth;
	private $ccExpireYear;
	private $cardType;
	private $customerBank;
	private $avsResp;
	private $cvv2Resp;
	private $authCode;
	private $masterId;
	private $name1;
	private $name2;
	private $paymentType;
	private $transType;
	private $amount;
	private $storeAccount;
	private $responseArray;

	public function __construct($responseArray)
	{    
		$this->$responseArray = $responseArray;
		$this->parseResponseArray($responseArray);
	}

	private function parseResponseArray($responseArray) 
	{
		$_o = array();
		parse_str($responseArray, $_o);
		$this->status = isset($_o['Result']) ? strtoupper($_o['Result']) : null;
		$this->message = isset($_o['MESSAGE']) ? strtoupper($_o['MESSAGE']) : null;
		$this->transId = isset($_o['RRNO']) ? strtoupper($_o['RRNO']) : null;
		$this->maskedAccount = isset($_o['PAYMENT_ACCOUNT']) ? strtoupper($_o['PAYMENT_ACCOUNT']) : null;
		$expires = isset($_o['CARD_EXPIRE']) ? strtoupper($_o['CARD_EXPIRE']) : null;
		$this->ccExpireYear = isset($expires) ? "20" . substr($expires, 2, 4) : null;
		$this->ccExpireMonth = isset($expires) ? substr($expires, 0, 2) : null;
		$this->cardType = isset($_o['CARD_TYPE']) ? strtoupper($_o['CARD_TYPE']) : null;
		$this->customerBank = isset($_o['BANK_NAME']) ? strtoupper($_o['BANK_NAME']) : null;
		$this->avsResp = isset($_o['AVS']) ? strtoupper($_o['AVS']) : null;
		$this->cvv2Resp = isset($_o['CVV2']) ? strtoupper($_o['CVV2']) : null;
		$this->authCode = isset($_o['AUTH_CODE']) ? strtoupper($_o['AUTH_CODE']) : null;
		$this->masterId = isset($_o['MASTER_ID']) ? strtoupper($_o['MASTER_ID']) : null;
		$this->name1 = isset($_o['NAME1']) ? strtoupper($_o['NAME1']) : null;
		$this->name2 = isset($_o['NAME2']) ? strtoupper($_o['NAME2']) : null;
		$this->paymentType = isset($_o['PAYMENT_TYPE']) ? strtoupper($_o['PAYMENT_TYPE']) : null;
		$this->transType = isset($_o['TRANS_TYPE']) ? strtoupper($_o['TRANS_TYPE']) : null;
		$this->amount = isset($_o['AMOUNT']) ? strtoupper($_o['AMOUNT']) : null;

	}

	public function getResponse() { return $this->responseArray; }
	public function getStatus() { return $this->status; }
	public function getMessage() { return $this->message; }
	public function getTransId() { return $this->transId; }
	public function getMaskedAccount() { return $this->maskedAccount; }
	public function getCcExpireMonth() { return $this->ccExpireMonth; }
	public function getCcExpireYear() { return $this->ccExpireYear; }
	public function getCardType() { return $this->cardType; }
	public function getBank() { return $this->customerBank; }
	public function getAVSResponse() { return $this->avsResp; }
	public function getCVV2Response() { return $this->cvv2Resp; }
	public function getAuthCode() { return $this->authCode; }
	public function getMasterId() { return $this->masterId; }
	public function getName1() { return $this->name1; }
	public function getName2() { return $this->name2; }
	public function getPaymentType() { return $this->paymentType; }
	public function getTransType() { return $this->transType; }
	public function getAmount() { return $this->amount; }
}