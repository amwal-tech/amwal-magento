<?php
declare(strict_types=1);

namespace Amwal\Payments\Observer;

use Amwal\Payments\Model\AmwalClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class SaveUtm implements ObserverInterface
{
    private CookieManagerInterface $cookieManager;
    private OrderRepositoryInterface $orderRepository;
    private JsonHelper $jsonHelper;
    private AmwalClientFactory $amwalClientFactory;

    public function __construct(
        CookieManagerInterface   $cookieManager,
        OrderRepositoryInterface $orderRepository,
        JsonHelper               $jsonHelper,
        AmwalClientFactory       $amwalClientFactory
    )
    {
        $this->cookieManager = $cookieManager;
        $this->orderRepository = $orderRepository;
        $this->jsonHelper = $jsonHelper;
        $this->amwalClientFactory = $amwalClientFactory;
    }

    /**
     * @param Observer $observer
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();

        $utmParameters = [
            'utm_source' => $this->cookieManager->getCookie('utm_source'),
            'utm_contact' => $this->cookieManager->getCookie('utm_contact'),
            'utm_medium' => $this->cookieManager->getCookie('utm_medium'),
            'utm_campaign' => $this->cookieManager->getCookie('utm_campaign'),
            'utm_term' => $this->cookieManager->getCookie('utm_term'),
            'utm_content' => $this->cookieManager->getCookie('utm_content')
        ];

        if (empty($utmParameters['utm_source'])) {
            return;
        }

        $order->setData('amwal_utm', $this->jsonHelper->jsonEncode($utmParameters));

        $this->orderRepository->save($order);

        $this->sendUtmToAmwal($order->getAmwalOrderId(), $utmParameters);
    }


    /**
     * Send UTM parameters to Amwal API
     * @param string $amwalOrderId
     * @param array $amwalUtm
     *
     * @throws NoSuchEntityException
     */
    private function sendUtmToAmwal(string $amwalOrderId, array $amwalUtm): void
    {
        $amwalClient = $this->amwalClientFactory->create();
        $orderDetails = [];
        $orderDetails['order_utm'] = $amwalUtm;
        try {
            $amwalClient->post(
                'transactions/' . $amwalOrderId . '/set_order_details',
                [
                    RequestOptions::JSON => $orderDetails
                ]
            );
        } catch (GuzzleException $e) {
            $message = sprintf(
                'Failed to set UTM parameters for order %s: %s',
                $amwalOrderId,
                $e->getMessage()
            );
            throw new RuntimeException($message);
        }
    }
}
