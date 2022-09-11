<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Config\Checkout;

use Amwal\Payments\Api\Data\RefIdDataInterfaceFactory;
use Amwal\Payments\Api\RefIdManagementInterface;
use Amwal\Payments\Model\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;

class ConfigProvider implements ConfigProviderInterface
{
    public const CODE = 'amwal_payments';

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var RefIdManagementInterface
     */
    private RefIdManagementInterface $refIdManagement;

    /**
     * @var RefIdDataInterfaceFactory
     */
    private RefIdDataInterfaceFactory $refIdDataFactory;

    /**
     * @var CustomerSession
     */
    private CustomerSession $customerSession;

    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;

    /**
     * @param Config $config
     * @param RefIdManagementInterface $refIdManagement
     * @param RefIdDataInterfaceFactory $refIdDataFactory
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Config $config,
        RefIdManagementInterface  $refIdManagement,
        RefIdDataInterfaceFactory $refIdDataFactory,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession
    ) {
        $this->config = $config;
        $this->refIdManagement = $refIdManagement;
        $this->refIdDataFactory = $refIdDataFactory;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $refIdData = $this->refIdDataFactory->create();
        $refIdData->setIdentifier($this->checkoutSession->getSessionId())
            ->setCustomerId((int) $this->customerSession->getCustomerId())
            ->setTimestamp(microtime())
            ->setSecret($this->config->getRefIdSecret());

        $refId = $this->refIdManagement->generateRefId($refIdData);

        $config = [
            'isActive' => $this->config->isActive(),
            'merchantId' => $this->config->getMerchantId(),
            'merchantMode' => $this->config->getMerchantMode(),
            'title' => $this->config->getTitle(),
            'countryCode' => $this->config->getCountryCode(),
            'locale' => $this->config->getLocale(),
            'darkMode' => $this->config->isDarkModeEnabled(),
            'currency' => $this->config->getCurrency(),
            'refId' => $refId,
            'refIdData' => $refIdData->toArray(),
        ];

        return [
            'payment' => [
                self::CODE => $config
            ]
        ];
    }
}
