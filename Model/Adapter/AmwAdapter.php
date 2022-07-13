<?php
namespace Amwal\Payments\Model\Adapter;

use Amwal\Payments\Gateway\Config\Config;

abstract class AmwAdapter
{
	const CONTENT_TYPE = 'application/json';
	const PROD_ENV = 'production';
	const SANDBOX_ENV = 'sandbox';
	const KEY_AMW_RESPONSE_CLIENT_TOKEN = 'Client-Token';
	const KEY_NONCE = 'Nonce';
	const KEY_PUBLIC_KEY = 'publicKeyBase64';
	const KEY_CLIENT_TOKEN = 'clientToken';
	const KEY_GATEWAY = 'gateway';
	const KEY_ACCOUNT_ID = 'accountId';
	const KEY_SECRET_KEY = 'secretKey';
	const KEY_ZERO_AUTH = 'zeroDollarAuth';
	const AUTH_ENDPOINT = 'merchant/authorize-session';
	/**
	 * @var Config
	 */
	protected $amwConfig;

	/**
	 * @var string
	 */
	protected $nonce;

	/**
	 * @var string
	 */
	protected $timestamp;

	/**
	 * Constructor
	 *
	 * @param Config $config
	 */
	public function __construct(
		Config $config
	) {
		$this->amwConfig = $config;
	}

	/**
	 * Retrieve assoc array
	 * authorization information
	 *
	 * @param string $storeId
	 * @return array
	 */
	public function getAuthData($storeId)
	{
		return;
	}

	/**
	 * Retrieve assoc array
	 * authorization information
	 *
	 * @param string $storeId
	 * @return array
	 */
	abstract protected function getGatewayData($storeId);

	/**
	 * Returns 'sandbox' or 'production'
	 * based on gateway's environment
	 *
	 * @param string $storeId
	 * @return string
	 */
	abstract protected function getEnvironment($storeId);
}