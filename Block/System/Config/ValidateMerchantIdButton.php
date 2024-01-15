<?php
declare(strict_types=1);

namespace Amwal\Payments\Block\System\Config;

use Amwal\Payments\Model\Config;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Widget\Button;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Url;

class ValidateMerchantIdButton extends Field
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->config = $config;
    }


    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Amwal_Payments::system/config/verify-merchant.phtml');
        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label' => __($originalData['button_label']),
                'html_id' => $element->getHtmlId(),
                'api_url' => $this->config->getApiBaseUrl() . '/merchant/check/'
            ]
        );

        return $this->_toHtml();
    }
}
