<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\Integration;

use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Api\Data\RefIdDataInterfaceFactory;
use Exception;
use JsonException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Catalog\ProductBuilder;
use TddWizard\Fixtures\Catalog\ProductFixture;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IntegrationTestBase extends TestCase
{
    private const INTEGRATION_TEST_CONFIG = [
        'currency/options/allow' => 'SAR,USD',
        'currency/options/base' => 'SAR',
        'currency/options/default' => 'SAR',
        'payment/amwal_payments/merchant_mode' => 'test',
        'payment/amwal_payments/merchant_id_valid' => 1,
        'payment/amwal_payments/merchant_id' => 'sandbox-amwal-e09ee380-d8c7-4710-a6ab-c9b39c7ffd47',
        'payment/amwal_payments/integration_test_run' => 1
    ];

    protected const MOCK_PRODUCT_SKU = 'amwal_simple';

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
     * @var ProductRepositoryInterface|null
     */
    private ?ProductRepositoryInterface $productRepository = null;

    /**
     * @var ProductFixture|null
     */
    private ?ProductFixture $productFixture = null;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->refIdDataFactory = $this->objectManager->get(RefIdDataInterfaceFactory::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->setupScopeConfig();
        $this->setupFixtures();
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function setupFixtures(): void
    {
        try {
            $product = $this->productRepository->get(self::MOCK_PRODUCT_SKU);
            $this->productFixture = new ProductFixture($product);
        } catch (NoSuchEntityException $e) {
            $this->productFixture = new ProductFixture(
                ProductBuilder::aSimpleProduct()
                    ->withSku(self::MOCK_PRODUCT_SKU)
                    ->withPrice(10)
                    ->build()
            );
        }

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

    /**
     * @return void
     */
    private function setupScopeConfig(): void
    {
        $scopeConfig = $this->objectManager->get(MutableScopeConfigInterface::class);
        foreach (self::INTEGRATION_TEST_CONFIG as $path => $value) {
            $scopeConfig->setValue($path, $value);
            $scopeConfig->setValue($path, $value, ScopeInterface::SCOPE_WEBSITE);
        }
    }
}
