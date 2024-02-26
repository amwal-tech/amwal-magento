<?php
declare(strict_types=1);

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId('simple')
    ->setId(1)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Amwal simple product')
    ->setSku('amwal_simple')
    ->setPrice(10)
    ->setMetaTitle('amwal simple product')
    ->setMetaKeyword('amwal simple product')
    ->setMetaDescription('amwal simple product')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setQty(100)
    ->setStockData(
        [
            'use_config_manage_stock'   => 1,
            'qty'                       => 100,
            'is_qty_decimal'            => 0,
            'is_in_stock'               => 1,
        ]
    )->save();

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$configWriter = $objectManager->get(\Magento\Framework\App\Config\Storage\WriterInterface::class);

// Set the configuration for the Amwal Payments module
$configWriter->save('payment/amwal_payments/merchant_id', 'sandbox-amwal-e09ee380-d8c7-4710-a6ab-c9b39c7ffd47');
$configWriter->save('payment/amwal_payments/active', 1);
$configWriter->save('payment/amwal_payments/merchant_id_valid', 1);
$configWriter->save('payment/amwal_payments/merchant_mode', 'test');
