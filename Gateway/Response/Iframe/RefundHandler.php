<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Response\Iframe;

use Magento\Sales\Model\Order\Payment;

class RefundHandler extends VoidHandler
{

	/**
	 * Sets payment transaction Id for non-refund, non-void transactions
	 * sets additional information for refund/void transactions
	 *
	 * @param Payment $payment
	 * @param \Amwal\Payments\Lib\Iframe\ApResponse $apResponse
	 * @return void
	 */
	protected function setTransactionId(Payment $payment, \Amwal\Payments\Lib\Iframe\ApResponse $apResponse) 
	{
		$payment->setTransactionAdditionalInfo(
			self::REFUND_ID,
			$apResponse->getTransId()
		);
	}

	/**
	 * Whether parent transaction should be closed
	 *
	 * @param Payment $payment
	 * @return bool
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	protected function shouldCloseParentTransaction(Payment $payment)
	{
		return !(bool)$payment->getCreditmemo()->getInvoice()->canRefund();
	}
}
