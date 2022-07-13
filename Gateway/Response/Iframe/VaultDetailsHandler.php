<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Response\Iframe;

use Amwal\Payments\Lib\Iframe\ApResponse;
use Amwal\Payments\Model\PaymentTokenFactory;
use Amwal\Payments\Gateway\Config\Iframe\Config;
use Amwal\Payments\Gateway\Subject\Iframe\SubjectReader;
use Amwal\Payments\Gateway\Response\Iframe\IfrDetailsHandler;
use Amwal\Payments\Gateway\Request\Iframe\VaultDataBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Vault Details Handler
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VaultDetailsHandler implements HandlerInterface
{
	/**
	 * @var PaymentTokenFactoryInterface
	 */
	protected $paymentTokenFactory;

	/**
	 * @var OrderPaymentExtensionInterfaceFactory
	 */
	protected $paymentExtensionFactory;

	/**
	 * @var SubjectReader
	 */
	protected $subjectReader;

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var Json
	 */
	private $serializer;

	/**
	 * VaultDetailsHandler constructor.
	 *
	 * @param PaymentTokenFactoryInterface $paymentTokenFactory
	 * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
	 * @param Config $config
	 * @param SubjectReader $subjectReader
	 * @param Json|null $serializer
	 * @throws \RuntimeException
	 */
	public function __construct(
		PaymentTokenFactoryInterface $paymentTokenFactory,
		OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
		Config $config,
		SubjectReader $subjectReader,
		Json $serializer = null
	) {
		$this->paymentTokenFactory = $paymentTokenFactory;
		$this->paymentExtensionFactory = $paymentExtensionFactory;
		$this->config = $config;
		$this->subjectReader = $subjectReader;
		$this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
	}

	/**
	 * @inheritdoc
	 */
	public function handle(array $handlingSubject, array $response)
	{
		$paymentDO = $this->subjectReader->readPayment($handlingSubject);
		/** @var \Amwal\Payments\Lib\Iframe\ApResponse $apResponse */
		$apResponse = $this->subjectReader->readApResponse($response);
		$payment = $paymentDO->getPayment();

		if ($payment->getStoreVault() === VaultDataBuilder::KEY_STORE_TRUE) {

			$paymentToken = null;
			if ($apResponse->getPaymentType() === "CREDIT") {
				$paymentToken = $this->getVaultCardToken($apResponse);
			} elseif ($apResponse->getPaymentType() === "IFR") {
				$paymentToken = $this->getVaultAccountToken($apResponse);
			}

			if ($paymentToken !== null) {
				$extensionAttributes = $this->getExtensionAttributes($payment);
				$extensionAttributes->setVaultPaymentToken($paymentToken);
			}
		}
	}

	/**
	 * Get vault payment token entity for payment card
	 *
	 * @param \Amwal\Payments\Lib\Iframe\ApResponse $apResponse
	 * @return PaymentTokenInterface|null
	 */
	protected function getVaultCardToken(ApResponse $apResponse)
	{
		/** @var PaymentTokenInterface $paymentToken */
		$paymentToken = $this->paymentTokenFactory->create(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD);

		$details = $this->convertDetailsToJSON([
			'type' => $this->getCreditCardType($apResponse->getCardType()),
			'maskedCC' => $apResponse->getMaskedAccount(),
			'expirationDate' => $apResponse->getCcExpireMonth() . "/" . $apResponse->getCcExpireYear()
		]);

		$paymentToken->setGatewayToken($apResponse->getMasterId());
		$paymentToken->setExpiresAt($this->getExpirationDate($apResponse));
		$paymentToken->setTokenDetails($details);

		return $paymentToken;
	}

	/**
	 * Get vault payment token entity for ach account
	 *
	 * @param \Amwal\Payments\Lib\Iframe\ApResponse $apResponse
	 * @return PaymentTokenInterface|null
	 */
	protected function getVaultAccountToken(ApResponse $apResponse)
	{
		$paymentToken = $this->paymentTokenFactory->create(PaymentTokenFactory::TOKEN_TYPE_IFR);

		$details = $this->convertDetailsToJSON(
			$this->parseAccountString($apResponse->getMaskedAccount())
		);

		$paymentToken->setTokenDetails($details);
		$paymentToken->setGatewayToken($apResponse->getMasterId());
		$expDate = date('Y-m-d 00:00:00', strtotime('+1 year'));
		$paymentToken->setExpiresAt($expDate);

		return $paymentToken;
	}

	private function parseAccountString($acctString)
	{
		$_r = array();
		preg_match(IfrDetailsHandler::ACCOUNT_PATTERN, $acctString, $_r);
		
		if (count($_r) < 4)
		{
			throw new \InvalidArgumentException('Unable to parse IFR account string from Iframe.');
		}
		
		return [
			IfrDetailsHandler::ACCOUNT_TYPE => $this->mapAccountType($_r[1]),
			IfrDetailsHandler::ROUTING_NUMBER => $_r[2],
			IfrDetailsHandler::BIN => $_r[3]
		];
	}

	private function mapAccountType($acctType) {
		if (strtoupper($acctType) == IfrDetailsHandler::SAVINGS_CODE)
		{
			return IfrDetailsHandler::SAVINGS;
		}
		if (strtoupper($acctType) == IfrDetailsHandler::CHECKING_CODE)
		{
			return IfrDetailsHandler::CHECKING;
		}

		throw new \InvalidArgumentException('Unable to identify Iframe IFR account type.');
	}

	/**
	 * @param ApResponse $apResponse
	 * @return string
	 */
	private function getExpirationDate($apResponse)
	{
		$expDate = new \DateTime(
            $apResponse->getCcExpireYear()
            . '-'
            . $apResponse->getCcExpireMonth()
            . '-'
            . '01'
            . ' '
            . '00:00:00',
            new \DateTimeZone('UTC')
        );
        $expDate->add(new \DateInterval('P1M'));
        
        return $expDate->format('Y-m-d 00:00:00');
	}

	/**
	 * Convert payment token details to JSON
	 * @param array $details
	 * @return string
	 */
	private function convertDetailsToJSON($details)
	{
		$json = $this->serializer->serialize($details);
		return $json ? $json : '{}';
	}

	/**
	 * Get type of credit card mapped from Iframe
	 *
	 * @param string $type
	 * @return array
	 */
	private function getCreditCardType($type)
	{
		$replaced = str_replace(' ', '-', strtolower($type));
		$mapper = $this->config->getCcTypesMapper();

		return $mapper[strtoupper($replaced)];
	}

	/**
	 * Get payment extension attributes
	 * @param InfoInterface $payment
	 * @return OrderPaymentExtensionInterface
	 */
	private function getExtensionAttributes(InfoInterface $payment)
	{
		$extensionAttributes = $payment->getExtensionAttributes();
		if (null === $extensionAttributes) {
			$extensionAttributes = $this->paymentExtensionFactory->create();
			$payment->setExtensionAttributes($extensionAttributes);
		}
		return $extensionAttributes;
	}
}
