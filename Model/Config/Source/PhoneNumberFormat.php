<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PhoneNumberFormat implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => 'raw',
                'label' => __('Raw')
            ]
        ];
        if (class_exists('libphonenumber\PhoneNumberFormat')) {
            $options[] = [
                'value' => libphonenumber\PhoneNumberFormat::NATIONAL,
                'label' => __('National')
            ];
            $options[] = [
                'value' => libphonenumber\PhoneNumberFormat::INTERNATIONAL,
                'label' => __('International')
            ];
            $options[] = [
                'value' => libphonenumber\PhoneNumberFormat::E164,
                'label' => __('E164')
            ];
            $options[] = [
                'value' => libphonenumber\PhoneNumberFormat::RFC3966,
                'label' => __('RFC3966')
            ];
        }
        $options[] = [
            'value' => 'country',
            'label' => __('Country based')
        ];

        return $options;
    }
}
