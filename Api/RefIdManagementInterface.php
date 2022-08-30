<?php
declare(strict_types=1);

namespace Amwal\Payments\Api;

use Amwal\Payments\Api\Data\RefIdDataInterface;

interface RefIdManagementInterface
{

    /**
     * @param \Amwal\Payments\Api\Data\RefIdDataInterface $refIdData
     * @return string
     */
    public function generateRefId(RefIdDataInterface $refIdData): string;

    /**
     * @param string $refId
     * @param \Amwal\Payments\Api\Data\RefIdDataInterface $refIdData
     * @return bool
     */
    public function verifyRefId(string $refId, RefIdDataInterface $refIdData): bool;
}
