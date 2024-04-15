<?php
declare(strict_types=1);

namespace Amwal\Payments\Plugin;

use Magento\Webapi\Controller\Rest;
use Magento\Framework\App\RequestInterface;

class RestApiCors
{
    /**
     * @param Rest $subject
     * @param RequestInterface $request
     * @phpcs:disable Magento2.Functions.DiscouragedFunction.Discouraged
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(Rest $subject, RequestInterface $request)
    {
        // Set CORS headers for API requests
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    }
}
