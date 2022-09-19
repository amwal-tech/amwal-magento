<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Data;

use Amwal\Payments\Api\Data\AmwalOrderItemInterface;
use Magento\Framework\DataObject;

class AmwalOrderItem extends DataObject implements \Amwal\Payments\Api\Data\AmwalOrderItemInterface
{

    /**
     * @inheritDoc
     */
    public function getProductId(): int
    {
        return (int) $this->getData(self::PRODUCT_ID);
    }

    /**
     * @inheritDoc
     */
    public function getConfiguredProductId(): ?int
    {
        return $this->getData(self::CONFIGURED_PRODUCT_ID);
    }

    /**
     * @inheritDoc
     */
    public function getSelectedConfigurableOptions(): array
    {
        return $this->getData(self::SELECTED_CONFIGURABLE_OPTIONS) ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getProductPrice(): float
    {
        return (float) $this->getData(self::PRODUCT_PRICE);
    }

    /**
     * @inheritDoc
     */
    public function getQty(): int
    {
        return (int) $this->getData(self::QTY);
    }

    /**
     * @inheritDoc
     */
    public function setProductId(int $productId): AmwalOrderItemInterface
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @inheritDoc
     */
    public function setConfiguredProductId(?int $configuredProductId): AmwalOrderItemInterface
    {
        return $this->setData(self::CONFIGURED_PRODUCT_ID, $configuredProductId);
    }

    /**
     * @inheritDoc
     */
    public function setSelectedConfigurableOptions(array $selectedConfigurableOptions): AmwalOrderItemInterface
    {
        return $this->setData(self::SELECTED_CONFIGURABLE_OPTIONS, $selectedConfigurableOptions);
    }

    /**
     * @inheritDoc
     */
    public function setProductPrice(float $productPrice): AmwalOrderItemInterface
    {
        return $this->setData(self::PRODUCT_PRICE, $productPrice);
    }

    /**
     * @inheritDoc
     */
    public function setQty(int $qty): AmwalOrderItemInterface
    {
        return $this->setData(self::QTY, $qty);
    }
}
