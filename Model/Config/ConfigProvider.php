<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Model\Config;

use Amwal\Payments\Gateway\Config\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Session\SessionManagerInterface;
use \Magento\Store\Model\StoreManagerInterface;

/**
 * Class ConfigProvider
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ConfigProvider implements ConfigProviderInterface
{
	const CODE = 'amwal_payments';

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var SessionManagerInterface
	 */
	private $session;

	/**
	* @var \Magento\Store\Model\StoreManagerInterface $storeManager
	*/
	private $storeManager;

	/**
	 * Constructor
	 *
	 * @param Config $config
	 * @param SessionManagerInterface $session
	 */
	public function __construct(
		Config $config,
		SessionManagerInterface $session,
		StoreManagerInterface $storeManager
	) {
		$this->config = $config;
		$this->session = $session;
		$this->storeManager = $storeManager;
	}

	/**
	 * Retrieve assoc array of checkout configuration
	 *
	 * @return array
	 */
	public function getConfig()
	{
		$storeId = $this->session->getStoreId();
		return [
			'payment' => [
				self::CODE => [
					'isActive' => $this->config->isActive($storeId),
					'storeUrl' => $this->getBaseUrl(),
				]
			]
		];
	}

	private function getBaseUrl()
	{
		return $this->storeManager->getStore()->getBaseUrl();
	}
}
