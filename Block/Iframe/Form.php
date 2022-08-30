<?php

namespace Amwal\Payments\Block\Iframe;

use Amwal\Payments\Gateway\Config\Iframe\Config as GatewayConfig;
use Amwal\Payments\Model\Config\Iframe\ConfigProvider as GatewayConfigProvider;
use Amwal\Payments\Model\Config\ConfigProvider as AmwConfigProvider;
use Amwal\Payments\Model\Source\Iframe\CcType;
use Magento\Framework\View\Element\Template\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Payment\Block\Form\Cc;
use Magento\Payment\Model\Config;

/**
 * Class Form
 */
class Form extends Cc
{
    const KEY_IFR = 'ifr';
    const KEY_CARD = 'payment-card';
    /**
     * @var Quote
     */
    protected $sessionQuote;

    /**
     * @var Config
     */
    protected $gatewayConfig;

    /**
     * @var CcType
     */
    protected $ccType;

    /**
     * @param Context $context
     * @param Config $paymentConfig
     * @param Quote $sessionQuote
     * @param GatewayConfig $gatewayConfig
     * @param CcType $ccType
     */
    public function __construct(
        Context $context,
        Config $paymentConfig,
        Quote $sessionQuote,
        GatewayConfig $gatewayConfig,
        CcType $ccType,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->sessionQuote = $sessionQuote;
        $this->gatewayConfig = $gatewayConfig;
        $this->ccType = $ccType;
    }


    public function getPaymentTypes()
    {
        $_types = $this->gatewayConfig->getPaymentType();
        $paymentTypes = array();

        if (strpos($_types, "IFR") !== false)
        {
            $paymentTypes[self::KEY_IFR] = "E-Check";
        }

        error_log(print_r($paymentTypes, true));
        return $paymentTypes;
    }

    public function canIfr()
    {
        return isset($this->getPaymentTypes()[self::KEY_IFR]);
    }

}
