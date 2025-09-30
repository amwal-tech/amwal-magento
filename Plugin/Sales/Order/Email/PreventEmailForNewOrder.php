<?php
declare(strict_types=1);

namespace Amwal\Payments\Plugin\Sales\Order\Email;

use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\Config\Checkout\ConfigProvider;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

/**
 * Prevent sending of the email on order placement for order placed with Amwal since we are still waiting for the payment
 */
class PreventEmailForNewOrder
{

    private Config $config;
    private const AMWAL_PAYMENT_METHODS = [
        'amwal_payments',
        'amwal_payments_bank_installments',
        'amwal_payments_apple_pay'
    ];


    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param OrderSender $subject
     * @param callable $proceed
     * @param Order $order
     * @param bool $forceSyncMode
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSend(OrderSender $subject, callable $proceed, Order $order, $forceSyncMode = false)
    {
        // If the prevention is disabled, proceed as normally
        if (!$this->config->isPreventOrderEmailEnabled()) {
            return $proceed($order, $forceSyncMode);
        }

        $payment = $order->getPayment();

        // Skip orders not paid with Amwal.
        if (!$payment || !in_array($payment->getMethod(), self::AMWAL_PAYMENT_METHODS, true)) {
            return $proceed($order, $forceSyncMode);
        }

        // Handle sending if the order is paid
        if ($order->getState() === $this->config->getOrderConfirmedStatus()) {
            return $proceed($order, $forceSyncMode);
        }

        return false;
    }
}
