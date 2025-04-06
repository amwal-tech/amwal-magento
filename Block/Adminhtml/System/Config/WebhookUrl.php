<?php
/**
 * Amwal Payments webhook URL block
 */
namespace Amwal\Payments\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Block for displaying webhook URL in system config
 */
class WebhookUrl extends Field
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
     * @param Context $context
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * Render the webhook URL field
     *
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        // Get the base URL
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        // Construct webhook URL - using REST API endpoint
        $webhookUrl = rtrim($baseUrl, '/') . '/rest/V1/amwal/webhook';

        // Render read-only field with copy button
        $html = '<div class="webhook-url-container">';
        $html .= '<input type="text" id="' . $element->getHtmlId() . '" ';
        $html .= 'name="' . $element->getName() . '" ';
        $html .= 'value="' . $webhookUrl . '" ';
        $html .= 'class="input-text admin__control-text" readonly="readonly" ';
        $html .= 'style="width: calc(100% - 50px);" />';
        $html .= '</div>';

        return $html;
    }
}
