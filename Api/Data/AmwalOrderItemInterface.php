<?php
declare(strict_types=1);

namespace Amwal\Payments\Api\Data;

interface AmwalOrderItemInterface
{
    public const PRODUCT_ID = 'product_id';
    public const CONFIGURED_PRODUCT_ID = 'configured_product_id';
    public const SELECTED_CONFIGURABLE_OPTIONS = 'selected_configurable_options';
    public const PRODUCT_PRICE = 'product_price';
    public const QTY = 'qty';

    /**
     * @return int
     */
    public function getProductId(): int;

    /**
     * @return int|null
     */
    public function getConfiguredProductId(): ?int;

    /**
     * @return string[]
     */
    public function getSelectedConfigurableOptions(): array;

    /**
     * @return float
     */
    public function getProductPrice(): float;

    /**
     * @return int
     */
    public function getQty(): int;

    /**
     * @param int $productId
     * @return AmwalOrderItemInterface
     */
    public function setProductId(int $productId): AmwalOrderItemInterface;

    /**
     * @param int|null $configuredProductId
     * @return AmwalOrderItemInterface
     */
    public function setConfiguredProductId(?int $configuredProductId): AmwalOrderItemInterface;

    /**
     * @param string[] $selectedConfigurableOptions
     * @return AmwalOrderItemInterface
     */
    public function setSelectedConfigurableOptions(array $selectedConfigurableOptions): AmwalOrderItemInterface;

    /**
     * @param float $productPrice
     * @return AmwalOrderItemInterface
     */
    public function setProductPrice(float $productPrice): AmwalOrderItemInterface;

    /**
     * @param int $qty
     * @return AmwalOrderItemInterface
     */
    public function setQty(int $qty): AmwalOrderItemInterface;
}
