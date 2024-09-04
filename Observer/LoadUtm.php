<?php
declare(strict_types=1);

namespace Amwal\Payments\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\App\RequestInterface;

class LoadUtm implements ObserverInterface
{
    private CookieManagerInterface $cookieManager;
    private CookieMetadataFactory $cookieMetadataFactory;
    private RequestInterface $request;

    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        RequestInterface $request
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->request = $request;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $utmParameters = [
            'utm_source' => $this->request->getParam('utm_source'),
            'utm_contact' => $this->request->getParam('utm_contact'),
            'utm_medium' => $this->request->getParam('utm_medium'),
            'utm_campaign' => $this->request->getParam('utm_campaign'),
            'utm_term' => $this->request->getParam('utm_term'),
            'utm_content' => $this->request->getParam('utm_content')
        ];

        // Set cookies for each UTM parameter
        foreach ($utmParameters as $key => $value) {
            if ($value) {
                $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                    ->setDuration(3600) // 1 hour duration
                    ->setPath('/')
                    ->setHttpOnly(false);

                $this->cookieManager->setPublicCookie($key, $value, $metadata);
            }
        }
    }
}
