<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Data;

use Amwal\Payments\Api\Data\RefIdDataInterface;
use Magento\Framework\DataObject;

class RefIdData extends DataObject implements RefIdDataInterface
{
    /**
     * @inheritDoc
     */
    public function setSecret(string $secret): RefIdDataInterface
    {
        return $this->setData(self::SECRET, $secret);
    }

    /**
     * @inheritDoc
     */
    public function setIdentifier(string $identifier): RefIdDataInterface
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId(int $customerId): RefIdDataInterface
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritDoc
     */
    public function setTimestamp(string $timestamp): RefIdDataInterface
    {
        return $this->setData(self::TIMESTAMP, $timestamp);
    }

    /**
     * @inheritDoc
     */
    public function getSecret(): string
    {
        return (string) $this->getData(self::SECRET);
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return (string) $this->getData(self::IDENTIFIER);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId(): int
    {
        return (int) $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function getTimestamp(): string
    {
        return (string) $this->getData(self::TIMESTAMP);
    }
}
