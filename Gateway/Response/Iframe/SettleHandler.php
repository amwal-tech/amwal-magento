<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Response\Iframe;

use \Magento\Sales\Model\Order\Payment;
use \Amwal\Payments\Lib\Iframe\ApResponse;

class SettleHandler extends TransactionIdHandler
{
	/**
	 * Sets payment transaction Id for non-refund, non-void transactions
	 * sets additional information for refund/void transactions
	 *
	 * @param Payment $payment
	 * @param \Amwal\Payments\Lib\Iframe\ApResponse $apResponse
	 * @return void
	 */
	protected function setTransactionId(Payment $payment, ApResponse $apResponse) 
	{
		$payment->setTransactionAdditionalInfo(
			self::CAPTURE_ID,
			$apResponse->getTransId()
		);
	}
}