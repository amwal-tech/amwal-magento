<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Block\Iframe;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;

/**
 * Class Info
 */
class Info extends ConfigurableInfo
{
	/**
	 * Returns label
	 *
	 * @param string $field
	 * @return Phrase
	 */
	protected function getLabel($field)
	{
		return __($field);
	}
}
