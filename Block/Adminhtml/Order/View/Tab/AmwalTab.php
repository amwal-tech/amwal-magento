<?php
declare(strict_types=1);

namespace Amwal\Payments\Block\Adminhtml\Order\View\Tab;

use Amwal\Payments\Model\AmwalClientFactory;
use Amwal\Payments\Model\Config;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

class AmwalTab extends Template implements TabInterface
{
    /**
     * @var Registry
     */
    protected Registry $registry;

    /**
     * @var AmwalClientFactory
     */
    protected AmwalClientFactory $amwalClientFactory;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Define the template file
     *
     * @var string
     */
    protected $_template = 'Amwal_Payments::order/view/tab/amwal_tab.phtml';

    /**
     * Constructor
     *
     * @param Context            $context
     * @param Registry           $registry
     * @param AmwalClientFactory $amwalClientFactory
     * @param Config             $config
     * @param LoggerInterface    $logger
     * @param array              $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AmwalClientFactory $amwalClientFactory,
        Config $config,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->amwalClientFactory = $amwalClientFactory;
        $this->config = $config;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve tab label.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Amwal Payments');
    }

    /**
     * Retrieve tab title.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Amwal Order Summary');
    }

    /**
     * Determine if the tab can be shown.
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Determine if the tab is hidden.
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Retrieve the current order from the registry.
     *
     * @return \Magento\Sales\Model\Order|null
     */
    public function getCurrentOrder()
    {
        return $this->registry->registry('current_order');
    }

    /**
     * Retrieve Amwal order data from the external API.
     *
     * @return string|null JSON string if successful, or null if not available.
     */
    public function getAmwalOrderData(): ?string
    {
        $order = $this->getCurrentOrder();
        if (!$order) {
            $this->logger->warning('No current order found in registry.');
            return null;
        }

        $amwalOrderId = $order->getAmwalOrderId();
        if (!$amwalOrderId) {
            return null;
        }

        try {
            $amwalClient = $this->amwalClientFactory->create();
            $response = $amwalClient->get('transactions/' . $amwalOrderId);

            if ($response->getStatusCode() === 200) {
                $amwalOrderData = $response->getBody()->getContents();
                return $amwalOrderData;
            } else {
                $this->logger->warning(sprintf(
                    'Amwal API returned non-200 status code for order ID "%s": %s',
                    $amwalOrderId,
                    $response->getStatusCode()
                ));
            }
        } catch (GuzzleException $e) {
            $this->logger->warning(sprintf(
                'Unable to retrieve Amwal order details for order ID "%s". Exception: %s',
                $amwalOrderId,
                $e->getMessage()
            ));
        }

        return null;
    }

    /**
     * Retrieve the Amwal order URL.
     *
     * @return string|null URL if available, or null if not available.
     */
    public function getAmwalOrderUrl(): ?string
    {
        $order = $this->getCurrentOrder();
        if (!$order) {
            $this->logger->warning('No current order found in registry.');
            return null;
        }

        $amwalOrderId = $order->getAmwalOrderId();
        if (!$amwalOrderId) {
            return null;
        }

        $amwalClient = $this->amwalClientFactory->create();
        $baseUrl = $amwalClient->getConfig('base_uri');

        return $baseUrl . 'transactions/' . $amwalOrderId;
    }
}
