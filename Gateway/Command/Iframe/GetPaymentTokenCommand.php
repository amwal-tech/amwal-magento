<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Amwal\Payments\Gateway\Command\Iframe;

use Magento\Payment\Gateway\Command\Result\ArrayResultFactory;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Amwal\Payments\Gateway\Subject\Iframe\SubjectReader;

/**
 * Class GetPaymentNonceCommand
 */
class GetPaymentTokenCommand implements CommandInterface
{

    /**
     * @var PaymentTokenManagementInterface
     */
    private $tokenManagement;

    /**
     * @var ArrayResultFactory
     */
    private $resultFactory;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param PaymentTokenManagementInterface $tokenManagement
     * @param BraintreeAdapterFactory $adapterFactory
     * @param ArrayResultFactory $resultFactory
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        PaymentTokenManagementInterface $tokenManagement,
        ArrayResultFactory $resultFactory,
        SubjectReader $subjectReader
    ) {
        $this->tokenManagement = $tokenManagement;
        $this->resultFactory = $resultFactory;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function execute(array $commandSubject)
    {
        $publicHash = $this->subjectReader->readPublicHash($commandSubject);
        $customerId = $this->subjectReader->readCustomerId($commandSubject);
        $paymentToken = $this->tokenManagement->getByPublicHash($publicHash, $customerId);
        if (!$paymentToken) {
            throw new \Exception('No available payment tokens');
        }

        return $this->resultFactory->create(['array' => ['paymentToken' => $paymentToken->getGatewayToken()]]);
    }
}
