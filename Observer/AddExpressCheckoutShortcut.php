<?php
namespace Amwal\Payments\Observer;

use Amwal\Payments\Block\ExpressCheckoutMinicartButton;
use Magento\Catalog\Block\ShortcutButtons;
use Magento\Checkout\Block\QuoteShortcutButtons;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class AddExpressCheckoutShortcut implements ObserverInterface
{
    public const EXPRESS_CHECKOUT_BLOCK = ExpressCheckoutMinicartButton::class;

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if (!$observer->getEvent()->getIsShoppingCart()) {
            return;
        }

        /** @var ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();
        $shortcut = $shortcutButtons->getLayout()->createBlock(self::EXPRESS_CHECKOUT_BLOCK);
        $shortcut->setIsCart(get_class($shortcutButtons) === QuoteShortcutButtons::class);
        $shortcutButtons->addShortcut($shortcut);
    }
}
