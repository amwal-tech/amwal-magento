<?php
declare(strict_types=1);

namespace Amwal\Payments\Plugin\Quote\Api;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Quote\Api\ChangeQuoteControlInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Override the quote change validator as we assign the quote to a newly created user, meaning the user context for
 * the initial call might be "guest", even though the user is later created an logged in.
 */
class OverrideChangeQuoteValidation
{
    private UserContextInterface $userContext;

    /**
     * @param UserContextInterface $userContext
     */
    public function __construct(UserContextInterface $userContext)
    {
        $this->userContext = $userContext;
    }

    /**
     * @param ChangeQuoteControlInterface $subject
     * @param bool $result
     * @param CartInterface $quote
     * @return bool
     */
    public function afterIsAllowed(ChangeQuoteControlInterface $subject, bool $result, CartInterface $quote): bool
    {
        if ($result === false) {
            if ($this->isAmwalUserCreated($quote)) {
                return true;
            }
        }

        return $result;
    }

    /**
     * @param CartInterface $quote
     * @return bool
     */
    private function isAmwalUserCreated(CartInterface $quote): bool
    {
        if (
            (int) $this->userContext->getUserType() === UserContextInterface::USER_TYPE_GUEST &&
            $quote->getAmwalUserCreated() === true
        ) {
            return true;
        }

        return false;
    }
}
