<?php
declare(strict_types=1);

namespace Amwal\Payments\Model;

use Magento\Payment\Model\Method\AbstractMethod;

class ApplePay extends AbstractMethod
{
    public const CODE = 'amwal_payments_apple_pay';

    protected $_code = self::CODE;
    protected $_isGateway = true;
    protected $_canCapture = false;
    protected $_canCapturePartial = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
}
