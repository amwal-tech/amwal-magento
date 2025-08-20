<?php
/**
 * Amwal Payments create API key button block
 */
namespace Amwal\Payments\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Block for the Create API Key button in system config
 */
class CreateApiKey extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Amwal_Payments::system/config/create_api_key.phtml';

    /**
     * Render the button
     *
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = $originalData['button_label'] ?? __('Register Webhook');
        $this->addData([
            'button_label' => $buttonLabel,
            'html_id' => $element->getHtmlId(),
            'ajax_url' => $this->_urlBuilder->getUrl('amwal/webhook/createapikey'),
        ]);

        return $this->_toHtml();
    }
}
