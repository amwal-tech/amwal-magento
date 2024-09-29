<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ModuleType implements OptionSourceInterface
{

    public const MODULE_TYPE_LITE = 'lite';
    public const MODULE_TYPE_PRO = 'pro';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::MODULE_TYPE_LITE,
                'label' => __('Lite')
            ],
            [
                'value' => self::MODULE_TYPE_PRO,
                'label' => __('Pro')
            ]
        ];
    }
}

