<?php
declare(strict_types=1);

/** @var $product Product */

use Amwal\Payments\Test\Integration\IntegrationTestBase;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

$productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);

try {
    $productRepository->get(IntegrationTestBase::MOCK_PRODUCT_SKU);
} catch (NoSuchEntityException $e) {
    $product = Bootstrap::getObjectManager()->create(ProductFactory::class)->create();
    $product->setTypeId('simple')
        ->setId(1)
        ->setAttributeSetId(4)
        ->setWebsiteIds([1])
        ->setName('Amwal simple product')
        ->setSku(IntegrationTestBase::MOCK_PRODUCT_SKU)
        ->setPrice(10)
        ->setMetaTitle('Amwal simple product')
        ->setMetaKeyword('Amwal simple product')
        ->setMetaDescription('Amwal simple product')
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setQty(100)
        ->setStockData(
            [
                'use_config_manage_stock'   => 1,
                'qty'                       => 100,
                'is_qty_decimal'            => 0,
                'is_in_stock'               => 1,
            ]
        );
}


