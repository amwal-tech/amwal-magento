<?php
namespace Amwal\Payments\Model\Adapter\Iframe;

use Amwal\Payments\Model\Adapter\AmwAdapter;
use Amwal\Payments\Gateway\Config\Config as AmwConfig;
use Amwal\Payments\Gateway\Config\Iframe\Config as IframeConfig;
use Amwal\Payments\Model\Source\Iframe\TransactionMode;

class IframeAdapter extends AmwAdapter
{
	const KEY_BP_GATEWAY = "AMWAL";
	/**
	 * @var Config
	 */
	protected $apConfig;

	/**
	 * Constructor
	 *
	 * @param Config $config
	 */
	public function __construct(
		AmwConfig $AmwConfig,
		IframeConfig $apConfig
	) {
		$this->apConfig = $apConfig;
		parent::__construct($AmwConfig);
	}

	/**
	 * Retrieve assoc array
	 * authorization information
	 *
	 * @param string $storeId
	 * @return array
	 */
	protected function getGatewayData($storeId)
	{
		$data = [];
		$data[self::KEY_GATEWAY] = self::KEY_BP_GATEWAY;
		$data[self::KEY_ACCOUNT_ID] = $this->apConfig->getMerchantId($storeId);
		//$data[self::KEY_SECRET_KEY] = $this->apConfig->getSecretKey($storeId);
		$data[self::KEY_ZERO_AUTH] = false;

		return $data;
	}

	/**
	 * Returns 'sandbox' or 'production'
	 * based on gateway's environment
	 *
	 * @param string $storeId
	 * @return string
	 */
	protected function getEnvironment($storeId)
	{
		$env = $this->apConfig->getEnvironment($storeId);

		if ($env == TransactionMode::TRANS_MODE_LIVE) {
			return self::PROD_ENV;
		} else if ($env == TransactionMode::TRANS_MODE_TEST) {
			return self::SANDBOX_ENV;
		}
	}
}
