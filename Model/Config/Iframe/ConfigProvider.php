<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Model\Config\Iframe;

use Amwal\Payments\Gateway\Config\Iframe\Config;
use Amwal\Payments\Model\Adapter\Iframe\IframeAdapter;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Checkout\Model\Cart;

/**
 * Class ConfigProvider
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ConfigProvider implements ConfigProviderInterface
{
    public const CODE = 'amwal_payments';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var IframeAdapter
     */
    private $amwAdapter;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    private $cart;

    /**
     * Constructor
     *
     * @param Config $config
     * @param SessionManagerInterface $session
     */
    public function __construct(
        Config $config,
        IframeAdapter $amwAdapter,
        SessionManagerInterface $session,
        Cart $cart
    ) {
        $this->config = $config;
        $this->amwAdapter = $amwAdapter;
        $this->session = $session;
        $this->cart = $cart;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $storeId = $this->session->getStoreId();
        $config = [
            'isActive' => $this->config->isActive($storeId),
            'merchantId' => $this->config->getMerchantId($storeId),
            'countryCode' => $this->config->getCountryCode($storeId),
            'preferredLang' => $this->config->getPreferredLang($storeId),
            'darkMode' => $this->config->getDarkMode($storeId),
            'amount' => $this->cart->getQuote()->getGrandTotal(),
            'paymentType' => $this->config->getPaymentType($storeId),
            'currency' => $this->config->getCurrency($storeId)

        ];


        if ($this->isIfrAvailable($config['paymentType'])) {
            //$config = array_merge($config, $this->gatherIfrConfigFields($storeId));
        }

        return [
            'payment' => [
                self::CODE => $config
            ]
        ];
    }

    private function isIfrAvailable(string $paymentType)
    {
        return strpos($paymentType, 'IFR') !== false;
    }

}
