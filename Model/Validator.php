<?php
declare(strict_types=1);

namespace Amwal\Payments\Model;

class Validator
{
    /**
     * Validate BIN codes
     *
     * @param string $value
     * @return bool
     */
    public function validateBinCodes(string $value): bool
    {
        $regex = '/^[0-9]{4,16}(?:,[0-9]{4,16})*$/';
        return (bool) preg_match($regex, $value);
    }
}
