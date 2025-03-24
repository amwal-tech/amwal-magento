<?php
declare(strict_types=1);

namespace Amwal\Payments\Api;

use Amwal\Payments\Api\Data\WebhookResponseInterface;
/**
 * Interface for webhook processing
 * @api
 */
interface WebHookInterface
{
    /**
     * Process incoming webhook data
     *
     * @param mixed $requestData
     * @return WebhookResponseInterface
     */
    public function execute();
}
