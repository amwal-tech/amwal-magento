<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Http\Iframe\Client;

use Amwal\Payments\Lib\Iframe\ApRequestKeys;

/**
 * A client that send transaction requests to the Amwal-Iframe Ap10emu API
 */
class TransactionAuth Extends ClientBase
{
	protected function getTransType() 
	{
		return self::TRANSACTION_TYPE_AUTH;
	}
}
