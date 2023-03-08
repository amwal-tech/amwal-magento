<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Config\Source;

use libphonenumber\PhoneNumberFormat as LibPhoneNumberFormat;
use Magento\Framework\Data\OptionSourceInterface;

class PhoneNumberFormat implements OptionSourceInterface
{
    public const FORMAT_RAW = 'raw';
    public const FORMAT_NATIONAL = LibPhoneNumberFormat::NATIONAL;
    public const FORMAT_INTERNATIONAL = LibPhoneNumberFormat::INTERNATIONAL;
    public const FORMAT_E164 = LibPhoneNumberFormat::E164;
    public const FORMAT_RFC3966 = LibPhoneNumberFormat::RFC3966;
    public const FORMAT_COUNTRY = 'country';

    public const UTILS_LIB_FORMATS = [
        self::FORMAT_NATIONAL,
        self::FORMAT_INTERNATIONAL,
        self::FORMAT_E164,
        self::FORMAT_RFC3966,
        self::FORMAT_COUNTRY
    ];

    public const OPTIONS = [
        self::FORMAT_RAW => 'Raw',
        self::FORMAT_NATIONAL => 'National',
        self::FORMAT_INTERNATIONAL => 'International',
        self::FORMAT_E164 => 'E164',
        self::FORMAT_RFC3966 => 'RFC3966',
        self::FORMAT_COUNTRY => 'Country based'
    ];


    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $options = [];
        foreach (self::OPTIONS as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => __($label)
            ];
        }

        return $options;
    }
}
