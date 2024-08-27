<?php
/**
 * Copyright © Youwe. All rights reserved.
 * https://www.youweagency.com
 */

declare(strict_types=1);

namespace Amwal\Payments\Plugin\Checkout;

use InvalidArgumentException;
use Magento\Checkout\Controller\Cart\Add;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Multishipping\Model\DisableMultishipping;
use Magento\Framework\Controller\Result\Json as JsonResult;

class EnrichAddToCartResponse
{

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @param ManagerInterface $messageManager
     * @param Json $json
     */
    public function __construct(
        ManagerInterface $messageManager,
        Json $json
    ) {
        $this->messageManager = $messageManager;
        $this->json = $json;
    }

    /**
     * @param Add $subject
     * @param ResponseInterface|ResultInterface $result
     * @return ResponseInterface|ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(Add $subject, $result)
    {
        $lastMessage = $this->messageManager->getMessages()->getLastAddedMessage();

        if (!$lastMessage || $lastMessage->getType() !== MessageInterface::TYPE_ERROR || !$lastMessage->getText()) {
            return $result;
        }

        // Check if the result is a JSON response
        if (!$result instanceof JsonResult) {
            return $result;
        }

        $existingContent = $result->getContent();

        if (!is_string($existingContent)) {
            return $result;
        }

        try {
            $content = $this->json->unserialize($existingContent);
        } catch (InvalidArgumentException $e) {
            return $result;
        }

        $content['error'] = $lastMessage->getText();

        $result->representJson(
            $this->json->serialize($content)
        );

        return $result;
    }
}
