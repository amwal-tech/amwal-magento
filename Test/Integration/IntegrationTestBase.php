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
     * @param string $method
     *
     * @return mixed
     * @throws JsonException
     */
    protected function executeAmwalCall(string $url, array $data, string $method = 'POST')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'content-type: application/json',
            'accept: */*',
            'authority: qa-backend.sa.amwal.tech',
            'amwal: ' . $data['merchantID'],
            'origin: https://store.amwal.tech',
            'referer: https://store.amwal.tech',
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true, 512, JSON_THROW_ON_ERROR);
    }
}
