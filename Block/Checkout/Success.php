<?php
declare(strict_types=1);

namespace Amwal\Payments\Block\Checkout;

use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\OrderFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Store\Model\StoreManagerInterface;
use Amwal\Payments\Model\AmwalClientFactory;
use Amwal\Payments\Model\Config;

class Success extends Template
{
    protected $orderFactory;
    protected $checkoutSession;
    protected $amwalClientFactory;
    protected $config;
    protected $storeManager;

    public function __construct(
        Template\Context $context,
        OrderFactory $orderFactory,
        CheckoutSession $checkoutSession,
        AmwalClientFactory $amwalClientFactory,
        Config $config,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderFactory = $orderFactory;
        $this->checkoutSession = $checkoutSession;
        $this->amwalClientFactory = $amwalClientFactory;
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    public function getOrder()
    {
        $orderId = $this->checkoutSession->getLastOrderId();
        return $this->orderFactory->create()->load($orderId);
    }


    public function getAmwalTransaction()
    {
        $order = $this->getOrder();
        $amwalClient = $this->amwalClientFactory->create();
        $response = $amwalClient->get('transactions/' . $order->getAmwalOrderId());

        if ($response->getStatusCode() === 200) {
            return json_decode($response->getBody()->getContents());
        }

        return null;
    }

    public function isBankInstallmentsEnabled()
    {
        return $this->config->isBankInstallmentsEnabled();
    }

    public function getInstallmentUrl()
    {
        $store = $this->storeManager->getStore();
        $url = 'https://pay.sa.amwal.tech/installment-setup';

        if ($store->getLocaleCode() && strpos($store->getLocaleCode(), 'ar') !== false) {
            $url .= '/ar';
        }

        return $url;
    }
}
