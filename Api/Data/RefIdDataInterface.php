<?php
declare(strict_types=1);

namespace Amwal\Payments\Api\Data;

interface RefIdDataInterface
{
    public const SECRET = 'secret';
    public const IDENTIFIER = 'identifier';
    public const CUSTOMER_ID = 'customer_id';
    public const TIMESTAMP = 'timestamp';

    /**
     * @param string $secret
     * @return RefIdDataInterface
     */
    public function setSecret(string $secret): RefIdDataInterface;

    /**
     * @param string $identifier
     * @return RefIdDataInterface
     */
    public function setIdentifier(string $identifier): RefIdDataInterface;

    /**
     * @param int $customerId
     * @return RefIdDataInterface
     */
    public function setCustomerId(int $customerId): RefIdDataInterface;

    /**
     * @param string $timestamp
     * @return RefIdDataInterface
     */
    public function setTimestamp(string $timestamp): RefIdDataInterface;

    /**
     * @return string
     */
    public function getSecret(): string;

    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * @return string
     */
    public function getTimestamp(): string;
}
