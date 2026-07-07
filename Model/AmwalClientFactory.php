<?php
declare(strict_types=1);

namespace Amwal\Payments\Model;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientFactory as GuzzleClientFactory;
use Magento\Framework\Encryption\EncryptorInterface;

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
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @param GuzzleClientFactory $guzzleClientFactory
     * @param Config $config
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        GuzzleClientFactory $guzzleClientFactory,
        Config $config,
        EncryptorInterface $encryptor
    ) {
        $this->guzzleClientFactory = $guzzleClientFactory;
        $this->config = $config;
        $this->encryptor = $encryptor;
    }

    /**
     * @return GuzzleClient
     */
    public function create(): GuzzleClient
    {
        $this->config->getApiUrl();

        $headers = [
            'Cache-Control' => 'nocache'
        ];

        $secretKey = $this->config->getSecretKey();
        if ($secretKey) {
            $headers['Authorization'] = $this->encryptor->decrypt($secretKey);
        }

        $config = [
            'base_uri' => rtrim($this->config->getApiUrl(), '/') . '/',
            'headers' => $headers,
        ];

        return $this->guzzleClientFactory->create(['config' => $config]);
    }
}

