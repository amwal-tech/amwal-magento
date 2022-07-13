<?php
/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Amwal_Payments
 * @copyright   Copyright (c) Amwal (https://www.amwal.tech/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

namespace Amwal\Payments\Block;

use Magento\Cms\Block\Block;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Zend_Serializer_Exception;

/**
 * Class StaticBlock
 * @package Magetop\Osc\Block
 */
class StaticBlock extends Template
{

    /**
     * StaticBlock constructor.
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {

        parent::__construct($context, $data);
    }

    /**
     * @return array
     * @throws Zend_Serializer_Exception
     */
    public function getStaticBlock()
    {
        try {
            $layout = $this->getLayout();
        } catch (LocalizedException $e) {
            $this->_logger->critical($e->getMessage());

            return [];
        }

        $result = [];

        
        
            /** @var Block $block */
            $block = $layout->createBlock(Block::class)->setBlockId('amwal_static_block')->toHtml();
            $name = 'amwal-static-block.top';

                $result[] = [
                    'content' => $block,
                    'sortOrder' => 0
                ];

        return $result;
    }
}
