<?php
declare(strict_types=1);

/** @var $product Product */

use Amwal\Payments\Test\Api\Model\Button\CartTest;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\TestFramework\Helper\Bootstrap;

$product = Bootstrap::getObjectManager()->create(Product::class);
$product->setTypeId('simple')
    ->setId(1)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Amwal simple product')
    ->setSku(CartTest::TEST_PRODUCT_SKU)
    ->setPrice(10)
    ->setMetaTitle('amwal simple product')
    ->setMetaKeyword('amwal simple product')
    ->setMetaDescription('amwal simple product')
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
    )->save();
