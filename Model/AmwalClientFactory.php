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
    private GuzzleClientFactory $guzzleClientFactory;

    /**
     * @var Config
     */
    private Config $config;

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
        $this->config->getApiUrl();

        $config = [
            'base_uri' => rtrim($this->config->getApiUrl(), '/') . '/',
            'headers' => [
                'Cache-Control' => 'nocache'
            ],
        ];

        return $this->guzzleClientFactory->create(['config' => $config]);
    }
}
