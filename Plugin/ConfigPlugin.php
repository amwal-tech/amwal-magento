<?php
declare(strict_types=1);

namespace Amwal\Payments\Plugin;

use Magento\Config\Model\Config;
use Amwal\Payments\Model\Validator;
use Magento\Framework\Exception\LocalizedException;

class ConfigPlugin
{
    /**
     * @var Validator
     */
    private Validator $validator;

    /**
     * ConfigPlugin constructor.
     * @param Validator $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Before save config
     *
     * @param Config $subject
     * @return void
     * @throws LocalizedException
     */
    public function beforeSave(Config $subject)
    {
        $groups = $subject->getGroups();
        $amwalGroup = $groups['amwal_payments']['groups']['amwal_payments_promotion']['fields']['cards_bin_codes'] ?? null;

        if ($amwalGroup !== null) {
            $value = $amwalGroup['value'] ?? '';
            if (!empty($value) && !$this->validator->validateBinCodes($value)) {
                throw new LocalizedException(__('[Amwal Payments][Promotion Settings] Please enter valid BIN codes. Each code should be 4 to 8 digits and separated by commas.'));
            }
        }

    }
}
