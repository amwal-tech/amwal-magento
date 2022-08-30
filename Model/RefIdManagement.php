<?php
declare(strict_types=1);

namespace Amwal\Payments\Model;

use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Api\RefIdManagementInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class RefIdManagement implements RefIdManagementInterface
{
    private EncryptorInterface $encryptor;

    /**
     * @param EncryptorInterface $encryptor
     */
    public function __construct(EncryptorInterface $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    /**
     * @inheritDoc
     */
    public function generateRefId(RefIdDataInterface $refIdData): string
    {
        return $this->encryptor->hash($refIdData->toString());
    }

    /**
     * @inheritDoc
     */
    public function verifyRefId(string $refId, RefIdDataInterface $refIdData): bool
    {
        $hash = $this->generateRefId($refIdData);
        return $refId === $hash;
    }
}
