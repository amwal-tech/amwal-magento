<?php

namespace Amwal\Payments\Block\Product;


use Amwal\Payments\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Math\Random;

class ProductsList extends \Magento\CatalogWidget\Block\Product\ProductsList
{
    protected $_template = 'Amwal_Payments::product/widget/content/grid.phtml';

    public const CHECKOUT_BUTTON_ID_PREFIX = 'amwal-checkout-button-';


    /**
     * @return string
     * @throws LocalizedException
     */
    public function getUniqueId($length = 8): string
    {
        $randomInstance = ObjectManager::getInstance()->get(Random::class);
        return self::CHECKOUT_BUTTON_ID_PREFIX . '-' . $randomInstance->getRandomString($length);
    }

    public function getTriggerContext(): string
    {
        return 'product-list-widget';
    }


    /**
     * @return bool
     */
    private function shouldRender(): bool
    {
        $config = ObjectManager::getInstance()->get(Config::class);
        if (!$config->isActive() || !$config->isExpressCheckoutActive()) {
            return false;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->shouldRender()) {
            return '';
        }
        return parent::_toHtml();
    }

}
