<?php
/**
 * Amwal Payments webhook events configuration block
 */
namespace Amwal\Payments\Block\Adminhtml\System\Config;

use Amwal\Payments\Model\Config;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Block for displaying webhook event options in system config
 */
class WebhookEvents extends Field
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var array
     */
    private array $availableEvents = [
        'order.success' => 'Order Success',
        'order.failed' => 'Order Failed'
    ];

    /**
     * @param Context $context
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * Render the webhook events selection field
     *
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $webhookEvents = $this->config->getWebhookEvents();
        $webhookEventsArray = explode(',', $webhookEvents);
        return $this->renderEventsList($webhookEventsArray);
    }

    /**
     * Render the list of available webhook events
     *
     * @param array $selectedEvents
     * @return string
     */
    private function renderEventsList(array $selectedEvents = []): string
    {
        $html = '<div class="webhook-events-container" style="margin-top: 15px;">';
        $html .= '<label>' . __('Available Webhook Events:') . '</label>';
        $html .= '<ul class="webhook-events-list" style="list-style-type: none; padding-left: 0;">';

        foreach ($this->availableEvents as $eventKey => $eventLabel) {
            $isChecked = in_array($eventKey, $selectedEvents) ? 'checked="checked"' : '';
            $html .= $this->renderEventCheckbox($eventKey, $eventLabel, $isChecked);
        }

        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render a single event checkbox
     *
     * @param string $eventKey
     * @param string $eventLabel
     * @param string $isChecked
     * @return string
     */
    private function renderEventCheckbox(string $eventKey, string $eventLabel, string $isChecked): string
    {
        $elementId = 'webhook_event_' . $eventKey;
        $html = '<li style="margin: 5px 0;">';
        $html .= '<input type="checkbox" id="' . $elementId . '" ';
        $html .= 'name="groups[amwal][fields][webhook_events][value][]" ';
        $html .= 'value="' . $eventKey . '" class="webhook-event-checkbox" ' . $isChecked . ' /> ';
        $html .= '<label for="' . $elementId . '">' . __($eventLabel) . '</label>';
        $html .= '</li>';

        return $html;
    }
}
