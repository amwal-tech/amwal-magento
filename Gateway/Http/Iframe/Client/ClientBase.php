<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Http\Iframe\Client;

use Amwal\Payments\Lib\Version;
use Amwal\Payments\Lib\Iframe\ApRequestKeys;
use Amwal\Payments\Lib\Iframe\ApResponse;
use Amwal\Payments\Gateway\Config\Iframe\Config;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger as PaymentLogger;
use Psr\Log\LoggerInterface;

/**
 * A client that send transaction requests to the Amwal-Iframe Ap10emu API
 */
abstract class ClientBase implements ClientInterface
{
	const TPS_ELEMENTS = [
		ApRequestKeys::MERCHANT_ID,
		ApRequestKeys::TRANSACTION_TYPE,
		ApRequestKeys::AMOUNT,
		ApRequestKeys::PAYMENT_TOKEN,
		ApRequestKeys::MODE
	];
	const TPS_HASH_TYPE = 'HMAC_SHA512';
	const TRANSACTION_TYPE_SALE = 'SALE';
	const TRANSACTION_TYPE_AUTH = 'AUTH';
	const TRANSACTION_TYPE_CAPTURE = 'CAPTURE';
	const TRANSACTION_TYPE_VOID = 'VOID';
	const TRANSACTION_TYPE_REFUND = 'REFUND';
	const USER_AGENT_PREFIX = 'Amwal-Iframe Magento 2 Plugin - v';
	const KEY_BP_RESPONSE = 'apResponse';
	const RESPONSE_VERSION = '5';

	/**
	 * @var PaymentLogger
	 */
	private $paymentLogger;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var ZendClientFactory
	 */
	private $httpClientFactory;

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @param PaymentLogger $paymentLogger
	 * @param LoggerInterface $logger
	 * @param ZendClientFactory $httpClientFactory
	 * @param Config $config
	 */
	public function __construct(
		PaymentLogger $paymentLogger,
		LoggerInterface $logger,
		ZendClientFactory $httpClientFactory,
		Config $config
	) {
		$this->httpClientFactory = $httpClientFactory;
		$this->config = $config;
		$this->paymentLogger = $paymentLogger;
		$this->logger = $logger;
	}

	/**
	 * Places request to gateway. Returns result as ApResponse object
	 *
	 * @param TransferInterface $transferObject
	 * @return Amwal\Payments\Lib\Iframe\ApResponse
	 * @throws \Magento\Payment\Gateway\Http\ClientException
	 */
	public function placeRequest(TransferInterface $transferObject) 
	{
		$requestBody = $transferObject->getBody();

		$storeId = $requestBody['store_id'] ?? null;
		$requestBody[ApRequestKeys::TRANSACTION_TYPE] = $this->getTransType();
		$requestBody[ApRequestKeys::MODE] = $this->config->getEnvironment($storeId);
		$requestBody[ApRequestKeys::TPS_DEFINITION] = implode(' ', self::TPS_ELEMENTS);
		$requestBody[ApRequestKeys::TPS_HASH_TYPE] = self::TPS_HASH_TYPE;
		$requestBody[ApRequestKeys::TPS] = $this->calculateTps($requestBody);
		$requestBody[ApRequestKeys::RESPONSE_VERSION] = self::RESPONSE_VERSION;

		// Remove unnecessary data from request
		unset($requestBody['store_id']);
		return $this->postToIframe($requestBody);
	}

	private function postToIframe(array $requestBody) 
	{
		$log = [
			'request' => $requestBody,
		];
		$url = $this->config->getApApiUrl();
		$userAgent = self::USER_AGENT_PREFIX . Version::getVersionString();

		$client = $this->httpClientFactory->create();
		$client->setUri($url);
		$client->setParameterPost($requestBody);
		$client->setMethod(ZendClient::POST);
		$client->setConfig([
			'maxredirects' => 0,
			'timeout' => 15,
			'useragent' => $userAgent,
		]);

		try {
			$response = $client->request();
			$apResponse = $this->parseResponse($response);
			$log['response'] = $apResponse[self::KEY_BP_RESPONSE]->getResponse();
			return $apResponse;
		} catch (\Exception $e) {

			$this->logger->critical($e);
			throw new ClientException(
				__('An error occurred in the payment gateway.')
			);
		} finally {
			$this->paymentLogger->debug($log);
		}
	}

	private function parseResponse($response)
	{
		$rawResponse = substr(
			$response->getHeader('location'),
			strpos($response->getHeader('location'), "?") + 1
		);

		if ($rawResponse)
		{
			$_res = array();
			$_res[self::KEY_BP_RESPONSE] = new ApResponse($rawResponse);
			
			return $_res;
		}

		throw new \Exception('Error parsing Iframe response.');
	}
	
	private function calculateTps(array $requestBody)
	{
		$storeId = $requestBody['store_id'] ?? null;
		$secretKey = $this->config->getValue(Config::KEY_SECRET_KEY, $storeId);

		$rawTpsData = '';
		foreach (self::TPS_ELEMENTS as $field) 
		{
			$_data = isset($requestBody[$field]) ? $requestBody[$field] : '';
			$rawTpsData = $rawTpsData . $_data;
		}

		return hash_hmac("sha512", $rawTpsData, $secretKey);
	}

	/**
	 * Get transaction type data
	 * @return string
	 */
	abstract protected function getTransType();
}
