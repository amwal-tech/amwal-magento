<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class TimelineStyle implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'default',
                'label' => __('Default')
            ],
            [
                'value' => 'simple',
                'label' => __('Simple')
            ]
        ];
    }
}

