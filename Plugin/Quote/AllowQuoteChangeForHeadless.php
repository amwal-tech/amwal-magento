<?php
declare(strict_types=1);

namespace Amwal\Payments\Plugin\Quote;

use Amwal\Payments\Model\Checkout\AmwalCheckoutAction;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\ChangeQuoteControl;

class AllowQuoteChangeForHeadless
{
    /**
     * @param ChangeQuoteControl $subject
     * @param bool $result
     * @param CartInterface $quote
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsAllowed(ChangeQuoteControl $subject, bool $result, CartInterface $quote): bool
    {
        if ($result !== true && $quote->getData(AmwalCheckoutAction::IS_AMWAL_API_CALL)) {
            $result = true;
        }

        return $result;
    }
}
