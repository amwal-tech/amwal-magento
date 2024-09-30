<?php
declare(strict_types=1);

namespace Amwal\Payments\Model;

use Magento\Payment\Model\Method\AbstractMethod;

class BankInstallments extends AbstractMethod
{
    public const CODE = 'amwal_payments_bank_installments';

    protected $_code = self::CODE;
    protected $_isGateway = true;
    protected $_canCapture = false;
    protected $_canCapturePartial = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
}
