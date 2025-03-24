<?php
declare(strict_types=1);
namespace Amwal\Payments\Model\Data;

use Amwal\Payments\Api\Data\WebhookResponseInterface;
use Magento\Framework\DataObject;

/**
 * Webhook response data model
 */
class WebhookResponse extends DataObject implements WebhookResponseInterface
{
    /**
     * Get success status
     *
     * @return bool
     */
    public function isSuccess()
    {
        return $this->getData(self::SUCCESS);
    }

    /**
     * Set success status
     *
     * @param bool $success
     * @return $this
     */
    public function setSuccess($success)
    {
        return $this->setData(self::SUCCESS, $success);
    }

    /**
     * Get response message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * Set response message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }
}
