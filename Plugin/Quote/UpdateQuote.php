<?php
declare(strict_types=1);

namespace Amwal\Payments\Plugin\Quote;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Amwal\Payments\Model\Checkout\AmwalCheckoutAction;
use Amwal\Payments\Model\Config\Checkout\ConfigProvider;

class UpdateQuote
{
    /**
     * Before submit quote plugin
     * @param QuoteManagement $subject
     * @param Quote $quote
     */
    public function beforeSubmit(QuoteManagement $subject, Quote $quote)
    {
        $quote->setData(AmwalCheckoutAction::IS_AMWAL_API_CALL, true);
        $quote->getPayment()->setQuote($quote);
        $quote->setPaymentMethod(ConfigProvider::CODE);
        $quote->getPayment()->importData(['method' => ConfigProvider::CODE]);
    }
}
