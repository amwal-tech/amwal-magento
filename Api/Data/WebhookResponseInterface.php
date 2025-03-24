<?php
declare(strict_types=1);

namespace Amwal\Payments\Api\Data;

/**
 * Interface for webhook response data
 * @api
 */
interface WebhookResponseInterface
{
    const SUCCESS = 'success';
    const MESSAGE = 'message';

    /**
     * Get success status
     *
     * @return bool
     */
    public function isSuccess();

    /**
     * Set success status
     *
     * @param bool $success
     * @return $this
     */
    public function setSuccess($success);

    /**
     * Get response message
     *
     * @return string
     */
    public function getMessage();

    /**
     * Set response message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message);
}
