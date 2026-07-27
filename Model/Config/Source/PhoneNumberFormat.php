<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Config\Source;

use libphonenumber\PhoneNumberFormat as LibPhoneNumberFormat;
use Magento\Framework\Data\OptionSourceInterface;

class PhoneNumberFormat implements OptionSourceInterface
{
    public const COUNTRY_OPTION_VALUE = 'country';

    /**
     * Normalizes a PhoneNumberFormat value to int.
     * Supports libphonenumber ^8.x (int constants) and ^9.x (int-backed enum).
     *
     * @param int|\BackedEnum $v
     * @return int
     * @phpcs:disable Magento2.Functions.StaticFunction.StaticFunction
     */
    private static function enumVal($v): int
    {
        return $v instanceof \BackedEnum ? $v->value : (int) $v;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        $options = [
            [
                'value' => 'raw',
                'label' => __('Raw')
            ]
        ];

        if (class_exists('libphonenumber\PhoneNumberFormat')) {
            $options[] = [
                'value' => self::enumVal(LibPhoneNumberFormat::NATIONAL),
                'label' => __('National')
            ];
            $options[] = [
                'value' => self::enumVal(LibPhoneNumberFormat::INTERNATIONAL),
                'label' => __('International')
            ];
            $options[] = [
                'value' => self::enumVal(LibPhoneNumberFormat::E164),
                'label' => __('E164')
            ];
            $options[] = [
                'value' => self::enumVal(LibPhoneNumberFormat::RFC3966),
                'label' => __('RFC3966')
            ];
        }

        $options[] = [
            'value' => self::COUNTRY_OPTION_VALUE,
            'label' => __('Country based')
        ];

        return $options;
    }

    /**
     * @return array<int|string>
     * @phpcs:disable Magento2.Functions.StaticFunction.StaticFunction
     */
    public static function getValidValues(): array
    {
        $values = [
            'raw',
            self::COUNTRY_OPTION_VALUE,
        ];

        if (class_exists('libphonenumber\PhoneNumberFormat')) {
            $values[] = self::enumVal(LibPhoneNumberFormat::NATIONAL);
            $values[] = self::enumVal(LibPhoneNumberFormat::INTERNATIONAL);
            $values[] = self::enumVal(LibPhoneNumberFormat::E164);
            $values[] = self::enumVal(LibPhoneNumberFormat::RFC3966);
        }

        return $values;
    }
}
