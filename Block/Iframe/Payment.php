<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Block\Iframe;

use Amwal\Payments\Model\Config\ConfigProvider as AmwConfigProvider;
use Amwal\Payments\Model\Config\Iframe\ConfigProvider as GatewayConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Payment
 *
 * @api
 * @since 100.1.0
 */
class Payment extends Template
{
	/**
	 * @var Amwal\Payments\Model\Config\ConfigProvider
	 */
	private $amwConfigProvider;

	/**
	 * @var Amwal\Payments\Model\Config\Iframe\ConfigProvider
	 */
	private $gatewayConfigProvider;

    /**
     * @var Json
     */
    private $json;

	/**
	 * Constructor
	 *
	 * @param Context $context
	 * @param AmwConfigProvider $amwConfigProvider
	 * @param GatewayConfigProvider $gatewayConfigProvider
	 * @param array $data
	 */
	public function __construct(
		Context $context,
		AmwConfigProvider $amwConfigProvider,
		GatewayConfigProvider $gatewayConfigProvider,
        Json $json,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->amwConfigProvider = $amwConfigProvider;
		$this->gatewayConfigProvider = $gatewayConfigProvider;
		$this->json = $json;
	}

	/**
	 * @return json object
	 */
	public function getAmwConfig()
	{
		$config = $this->amwConfigProvider->getConfig();
		return $this->json->serialize($config);
	}

	public function getGatewayConfig() 
	{
		$config = $this->gatewayConfigProvider->getConfig();
		return $this->json->serialize($config);
	}

	/**
	 * @return json object
	 */
	public function getCode()
	{
		return GatewayConfigProvider::CODE;
	}
}
