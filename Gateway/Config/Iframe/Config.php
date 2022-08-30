<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Config\Iframe;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    // Gets config values using field names
    public const KEY_ACTIVE = 'active';
    public const KEY_MERCHANT_ID = 'merchant_id';
    public const KEY_COUNTRY_CODE = 'country_code';
    public const KEY_PREFERRED_LANG = 'preferred_lang';
    public const KEY_DARK_MODE = 'dark_mode';
    public const KEY_ENVIRONMENT = 'trans_mode';
    public const KEY_PAYMENT_TYPE = 'payment_type';
    public const KEY_PAYMENT_ACTION = 'payment_action';
    public const KEY_CC_TYPES = 'cctypes';
    public const KEY_USE_CCV = 'useccv';
    public const KEY_CURRENCY = 'currency';
    public const KEY_CC_TYPES_MAPPER ='cc_types_iframe_mapper';
    public const KEY_API_URL = 'api_endpoint';
    public const KEY_IFR_SHPF_URL = 'ifr_shpf_url';

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
        $pathPattern = self::DEFAULT_PATH_PATTERN,
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
     * Returns Iframe account id.
     *
     * @return string
     */
    public function getMerchantId($storeId = null)
    {
        return $this->getValue(self::KEY_MERCHANT_ID, $storeId);
    }

    /**
     * Returns Iframe secret key.
     *
     * @return string
     */
    public function getCountryCode($storeId = null)
    {
        return $this->getValue(self::KEY_COUNTRY_CODE, $storeId);
    }


    public function getPreferredLang($storeId = null)
    {
        return $this->getValue(self::KEY_PREFERRED_LANG, $storeId);
    }


    public function getDarkMode($storeId = null)
    {
        return $this->getValue(self::KEY_DARK_MODE, $storeId);
    }

    /**
     * Gets value of Iframe transaction mode.
     *
     * Possible values: live or test.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getEnvironment($storeId = null)
    {
        return $this->getValue(self::KEY_ENVIRONMENT, $storeId);
    }

    /**
     * Gets value of Iframe payment type.
     *
     * Possible values: CCIFR, CC, or IFR.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getPaymentType($storeId = null)
    {
        return $this->getValue(self::KEY_PAYMENT_TYPE, $storeId);
    }

    /**
     * Gets value of Iframe payment action.
     *
     * Possible values: Sale or Authorize Only.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getPaymentAction($storeId = null)
    {
        return $this->getValue(self::KEY_PAYMENT_ACTION, $storeId);
    }


    /**
     * Retrieve available credit card types
     *
     * @param int|null $storeId
     * @return array
     */
    public function getAvailableCardTypes($storeId = null)
    {
        $ccTypes = $this->getValue(self::KEY_CC_TYPES, $storeId);

        return !empty($ccTypes) ? explode(',', $ccTypes) : [];
    }

    /**
     * Checks if ccv field is enabled.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isCcvEnabled($storeId = null)
    {
        return (bool) $this->getValue(self::KEY_USE_CCV, $storeId);
    }

    /**
     * Gets value of configured currency.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getCurrency($storeId = null)
    {
        return $this->getValue(self::KEY_CURRENCY, $storeId);
    }

    /**
     * Returns Iframe BP10emu API URL.
     *
     * @return string
     */
    public function getApApiUrl()
    {
        return $this->getValue(self::KEY_API_URL);
    }

    /**
     * Retrieve mapper between Magento and Iframe card types
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

    /**
     * Returns URL of the Iframe SHPF
     * used for $0 IFR Authorization transactions
     *
     * @return string
     */
    public function getIfrShpfUrl()
    {
        return $this->getValue(self::KEY_IFR_SHPF_URL);
    }

}
