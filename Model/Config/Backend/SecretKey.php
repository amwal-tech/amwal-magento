<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Config\Backend;

use Amwal\Payments\Model\Config;
use Magento\Config\Model\Config\Backend\Encrypted;
use Magento\Framework\Exception\LocalizedException;

class SecretKey extends Encrypted
{
    /**
     * Validate and process secret key before saving configuration
     *
     * @return void
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = (string)$this->getValue();

        // If the value was not modified in the admin form, it consists of asterisks (******)
        if (preg_match('/^\*+$/', $value)) {
            $value = (string)$this->getOldValue();
        }

        if ($this->isAmwalActive() && empty(trim($value))) {
            throw new LocalizedException(
                __('The Secret Key is required when Amwal Payments is enabled.')
            );
        }

        parent::beforeSave();
    }

    /**
     * Determine if Amwal Payments is active or being activated
     *
     * @return bool
     */
    private function isAmwalActive(): bool
    {
        $groups = $this->getData('groups');
        if (isset($groups['amwal_payments']['fields']['active']['value'])) {
            return (bool)$groups['amwal_payments']['fields']['active']['value'];
        }

        $activeField = $this->getFieldsetDataValue('active');
        if ($activeField !== null) {
            return (bool)$activeField;
        }

        return $this->_config->isSetFlag(
            Config::XML_CONFIG_PATH_ACTIVE,
            $this->getScope(),
            $this->getScopeId()
        );
    }
}
