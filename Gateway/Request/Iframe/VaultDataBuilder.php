<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Request\Iframe;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Amwal\Payments\Lib\Iframe\ApRequestKeys;
use Amwal\Payments\Gateway\Subject\Iframe\SubjectReader;
use Magento\Vault\Model\Ui\VaultConfigProvider;

/**
 * Vault Data Builder
 */
class VaultDataBuilder implements BuilderInterface
{
	const KEY_STORE_TRUE = 'T';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {   

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();

        $result = [];
        $data = $payment->getAdditionalInformation();
        if (!empty($data[VaultConfigProvider::IS_ACTIVE_CODE])) {
            $payment->setStoreVault(self::KEY_STORE_TRUE);
            $result[ApRequestKeys::STORE_ACCOUNT] = self::KEY_STORE_TRUE;
        }

        return $result;
    }
}
