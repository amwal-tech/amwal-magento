<?php
declare(strict_types=1);

namespace Amwal\Payments\Model;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientFactory as GuzzleClientFactory;

class AmwalClientFactory
{

    /**
     * @var GuzzleClientFactory
     */
    private $guzzleClientFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param GuzzleClientFactory $guzzleClientFactory
     * @param Config $config
     */
    public function __construct(
        GuzzleClientFactory $guzzleClientFactory,
        Config $config
    ) {
        $this->guzzleClientFactory = $guzzleClientFactory;
        $this->config = $config;
    }

    /**
     * @return GuzzleClient
     */
    public function create(): GuzzleClient
    {
        $this->config->getApiBaseUrl();

        $config = [
            'base_uri' => rtrim($this->config->getApiBaseUrl(), '/') . '/',
            'headers' => [
                'Cache-Control' => 'nocache'
            ],
        ];

        return $this->guzzleClientFactory->create(['config' => $config]);
    }
}
