<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Config\Source\Status;

use Magento\Sales\Model\Config\Source\Order\Status;
use Magento\Sales\Model\Order;

class OrderCreation extends Status
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $statuses = $this->_orderConfig->getStateStatuses(
            $this->getOrderCreationStatuses()
        );

        $options = [];
        foreach ($statuses as $code => $label) {
            $options[] = [
                'value' => $code,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getOrderCreationStatuses(): array
    {
        return [
            Order::STATE_NEW,
            Order::STATE_PENDING_PAYMENT
        ];
    }
}

