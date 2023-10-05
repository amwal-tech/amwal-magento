<?php
declare(strict_types=1);

namespace Amwal\Payments\Plugin;

use Magento\Webapi\Controller\Rest;

class RestApiCors
{
    public function beforeDispatch(Rest $subject, $request)
    {
        // Set CORS headers for API requests
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    }
}
