<?php
declare(strict_types=1);

namespace Amwal\Payments\Plugin\Sales\Order\View;

use GuzzleHttp\Exception\GuzzleException;
use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\App\RequestInterface;
use Amwal\Payments\Model\AmwalClientFactory;
use Amwal\Payments\Model\Config;

class Details
{
    protected Session $authSession;
    protected UrlInterface $urlBuilder;
    protected OrderInterface $order;
    protected RequestInterface $request;
    protected AmwalClientFactory $amwalClientFactory;
    private Config $config;

    public function __construct(
        Session            $authSession,
        UrlInterface       $urlBuilder,
        OrderInterface     $order,
        RequestInterface   $request,
        AmwalClientFactory $amwalClientFactory,
        Config             $config
    ) {
        $this->authSession = $authSession;
        $this->urlBuilder = $urlBuilder;
        $this->order = $order;
        $this->request = $request;
        $this->amwalClientFactory = $amwalClientFactory;
        $this->config = $config;
    }

    public function beforeSetLayout(View $subject)
    {
        $amwalOrderId = $subject->getOrder()->getAmwalOrderId();
        $amwalClient = $this->amwalClientFactory->create();

        try {
            // check if the $amwalOrderId have -canceled suffix
            if (strpos($amwalOrderId, '-canceled') !== false) {
                return;
            }

            $response = $amwalClient->get('transactions/' . $amwalOrderId);

            if ($response->getStatusCode() === 200) {
                $responseBody = $response->getBody()->getContents();
                $amwalOrderStatus = json_decode($responseBody)->status;

                $subject->addButton(
                    'amwal_order_details',
                    [
                        'label' => __('Amwal Order Details'),
                        'class' => $this->isPayValid($subject->getOrder()->getState(), $amwalOrderStatus) ? '' : 'hidden',
                        'data_attribute' => [
                            'mage-init' => [
                                'Amwal_Payments/js/order-details' => [
                                    'buttonId' => 'amwal_order_details',
                                    'order_id' => $subject->getOrder()->getIncrementId(),
                                    'amwal_order_id' => $amwalOrderId,
                                    'order_details' => $responseBody
                                ]
                            ]
                        ],
                    ]
                );
            }
        } catch (GuzzleException $e) {
            $message = sprintf(
                'Unable to set Order details in Amwal for order with ID "%s". Exception: %s',
                $amwalOrderId,
                $e->getMessage()
            );
            return;
        }
    }

    private function isPayValid($orderState, $amwalOrderStatus)
    {
        $defaultOrderStatus = $this->config->getOrderConfirmedStatus();

        if ($orderState === $defaultOrderStatus) {
            return false;
        }
        return $orderState === 'pending_payment' || $orderState === 'canceled';
    }
}
