<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\Integration;

use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Api\Data\RefIdDataInterfaceFactory;
use JsonException;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IntegrationTestBase extends TestCase
{
    public const MOCK_PRODUCT_SKU = 'amwal_simple';

    protected const MOCK_REF_ID_DATA = [
        RefIdDataInterface::IDENTIFIER => '100',
        RefIdDataInterface::CUSTOMER_ID => '0',
        RefIdDataInterface::TIMESTAMP => '1707916143'
    ];

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var RefIdDataInterfaceFactory|null
     */
    private ?RefIdDataInterfaceFactory $refIdDataFactory = null;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->refIdDataFactory = $this->objectManager->get(RefIdDataInterfaceFactory::class);
    }

    /**
     * @return RefIdDataInterface
     */
    protected function getMockRefIdData(): RefIdDataInterface
    {
        /** @var RefIdDataInterface $refIdData */
        $refIdData = $this->refIdDataFactory->create();
        $refIdData->setData(self::MOCK_REF_ID_DATA);

        return $refIdData;
    }

    /**
     * @param string $url
     * @param array $data
     * @param string $merchantId
     * @param string $method
     *
     * @return mixed
     * @throws JsonException
     */
    protected function executeAmwalCall(string $url, array $data, string $merchantId, string $method = 'POST')
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'authority: qa-backend.sa.amwal.tech',
                'accept: */*',
                'amwal: ' . $merchantId,
                'content-type: application/json',
                'origin: https://store.amwal.tech',
                'referer: https://store.amwal.tech/'
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }
}
