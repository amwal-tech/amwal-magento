<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class MerchantMode implements OptionSourceInterface
{

    public const MERCHANT_TEST_MODE = 'test';
    public const MERCHANT_LIVE_MODE = 'live';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::MERCHANT_TEST_MODE,
                'label' => __('Test')
            ],
            [
                'value' => self::MERCHANT_LIVE_MODE,
                'label' => __('Live')
            ]
        ];
    }
}

