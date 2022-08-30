<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Config\Checkout;

use Amwal\Payments\Model\Config;
use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    public const CODE = 'amwal_payments';

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [
            'isActive' => $this->config->isActive(),
            'merchantId' => $this->config->getMerchantId(),
            'merchantMode' => $this->config->getMerchantMode(),
            'title' => $this->config->getTitle(),
            'countryCode' => $this->config->getCountryCode(),
            'locale' => $this->config->getLocale(),
            'darkMode' => $this->config->isDarkModeEnabled(),
            'currency' => $this->config->getCurrency(),
        ];

        return [
            'payment' => [
                self::CODE => $config
            ]
        ];
    }
}
