<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{

	const PATH_PATTERN = '%s/%s';

	// Gets config values using field names
	const KEY_ACTIVE = 'active';
	const KEY_AMW_KEY = 'amw_key';
	const KEY_AMW_SECRET = 'amw_secret';
	const KEY_SANDBOX_LIB_URL = 'amw_uat_client_url';
	const KEY_PROD_LIB_URL = 'amw_prod_client_url';
	const KEY_PROD_SERVICE_URL = 'amw_prod_service_url';
	const KEY_SANDBOX_SERVICE_URL = 'amw_sandbox_service_url';
	const KEY_CC_TYPES_MAPPER ='cc_types_amw_mapper';
	/**
	 * @var \Magento\Framework\Serialize\Serializer\Json
	 */
	private $serializer;

	/**
	 * Amwal Iframe config constructor
	 *
	 * @param ScopeConfigInterface $scopeConfig
	 * @param null|string $methodCode
	 * @param string $pathPattern
	 * @param Json|null $serializer
	 */
	public function __construct(
		ScopeConfigInterface $scopeConfig,
		$methodCode = null,
		$pathPattern = self::PATH_PATTERN,
		Json $serializer = null
	) {
		parent::__construct($scopeConfig, $methodCode, $pathPattern);
		$this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
			->get(Json::class);
	}

	/**
	 * Gets Payment configuration status.
	 *
	 * @param int|null $storeId
	 * @return bool
	 */
	public function isActive($storeId = null)
	{
		return (bool) $this->getValue(self::KEY_ACTIVE, $storeId);
	}

	/**
	 * Returns  key.
	 *
	 * @return string
	 */
	public function getAmwKey()
	{
		return $this->getValue(self::KEY_AMW_KEY);
	}

	/**
	 * Returns  secret.
	 *
	 * @return string
	 */
	public function getAmwSecret()
	{
		return $this->getValue(self::KEY_AMW_SECRET);
	}

	/**
	 * Returns sandbox SDK url.
	 *
	 * @return string
	 */
	public function getAmwUatLibUrl()
	{
		return $this->getValue(self::KEY_SANDBOX_LIB_URL);
	}

	/**
	 * Returns productino SDK url.
	 *
	 * @return string
	 */
	public function getAmwProdLibUrl()
	{
		return $this->getValue(self::KEY_PROD_LIB_URL);
	}

	/**
	 * Returns  production service URL.
	 *
	 * @return string
	 */
	public function getAmwProdUrl()
	{
		return $this->getValue(self::KEY_PROD_SERVICE_URL);
	}

	/**
	 * Returns  sandbox service URL.
	 *
	 * @return string
	 */
	public function getAmwSandboxUrl()
	{
		return $this->getValue(self::KEY_SANDBOX_SERVICE_URL);
	}

	/**
	 * Retrieve mapper between Magento and AMW card types
	 *
	 * @return array
	 */
	public function getCcTypesMapper()
	{
		$result = json_decode(
			$this->getValue(self::KEY_CC_TYPES_MAPPER),
			true
		);

		return is_array($result) ? $result : [];
	}

}
