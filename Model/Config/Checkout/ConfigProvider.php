<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Config\Checkout;

use Amwal\Payments\Api\Data\RefIdDataInterfaceFactory;
use Amwal\Payments\Api\RefIdManagementInterface;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\ThirdParty\CityHelper;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\UrlInterface;

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
     * @var CityHelper
     */
    private CityHelper $cityHelper;

    /**
     * @var DirectoryHelper
     */
    private DirectoryHelper $directoryHelper;

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlInterface;

    /**
     * @param Config $config
     * @param RefIdManagementInterface $refIdManagement
     * @param RefIdDataInterfaceFactory $refIdDataFactory
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param CityHelper $cityHelper
     * @param DirectoryHelper $directoryHelper
     * @param UrlInterface $urlInterface
     */
    public function __construct(
        Config $config,
        RefIdManagementInterface  $refIdManagement,
        RefIdDataInterfaceFactory $refIdDataFactory,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        CityHelper $cityHelper,
        DirectoryHelper $directoryHelper,
        UrlInterface $urlInterface
    ) {
        $this->config = $config;
        $this->refIdManagement = $refIdManagement;
        $this->refIdDataFactory = $refIdDataFactory;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->cityHelper = $cityHelper;
        $this->directoryHelper = $directoryHelper;
        $this->urlInterface = $urlInterface;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $customerId = (int) $this->customerSession->getCustomerId();
        $refIdData = $this->refIdDataFactory->create();
        $refIdData->setIdentifier($this->checkoutSession->getSessionId())
            ->setCustomerId($customerId)
            ->setTimestamp(microtime());

        $refId = $this->refIdManagement->generateRefId($refIdData);

        $config = [
            'isActive' => $this->config->isActive(),
            'isRegularCheckoutActive' => $this->config->isRegularCheckoutActive(),
            'isExpressCheckoutActive' => $this->config->isExpressCheckoutActive(),
            'merchantId' => $this->config->getMerchantId(),
            'merchantMode' => $this->config->getMerchantMode(),
            'title' => $this->config->getTitle(),
            'countryCode' => $this->config->getCountryCode(),
            'locale' => $this->config->getLocale(),
            'darkMode' => $this->config->isDarkModeEnabled(),
            'currency' => $this->config->getCurrency(),
            'refId' => $refId,
            'refIdData' => $refIdData->toArray(),
            'allowedCountries' => array_keys($this->directoryHelper->getCountryCollection()->getItems()),
            'allowedAddressStates' => $this->config->getLimitedRegionsArray(),
            'allowedAddressCities' => $this->cityHelper->getCityCodes(),
            'pluginVersion' => $this->config->getVersion(),
            'useBaseCurrency' => $this->config->shouldUseBaseCurrency(),
            'isApplePayActive' => $this->config->isApplePayActive(),
            'isBankInstallmentsActive' => $this->config->isBankInstallmentsActive(),
            'defaultRedirectUrl' => $this->urlInterface->getUrl('amwal/redirect'),
            'isRegularCheckoutRedirect' => $this->config->isRegularCheckoutRedirect(),
        ];

        return [
            'payment' => [
                self::CODE => $config
            ]
        ];
    }
}
