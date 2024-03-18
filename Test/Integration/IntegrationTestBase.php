<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\Integration;

use _PHPStan_c862bb974\Symfony\Component\Console\Exception\RuntimeException;
use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Api\Data\RefIdDataInterfaceFactory;
use Amwal\Payments\Model\AmwalClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
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
     * @var AmwalClientFactory|null
     */
    private ?AmwalClientFactory $amwalClientFactory = null;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->refIdDataFactory = $this->objectManager->get(RefIdDataInterfaceFactory::class);
        $this->amwalClientFactory = $this->objectManager->get(AmwalClientFactory::class);
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
     * @param string $uri
     * @param array $data
     * @param string $method
     *
     * @return mixed
     * @throws JsonException
     */
    protected function executeAmwalCall(string $uri, array $data, string $method = 'POST')
    {
        $amwalClient = $this->amwalClientFactory->create();
        try {
            $response = $amwalClient->request(
                $method,
                $uri,
                [
                    RequestOptions::JSON => $data
                ]
            );
        } catch (GuzzleException $e) {
            throw new RuntimeException($e->getResponse()->getBody()->getContents());
        }

        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }
}
