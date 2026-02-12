<?php
declare(strict_types=1);

namespace Amwal\Payments\Block\System\Config;

use Amwal\Payments\Model\Config;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Custom button field for validating Merchant ID
 *
 * Renders a button that triggers AJAX validation of the merchant credentials
 * against the Amwal API endpoint.
 */
class ValidateMerchantIdButton extends Field
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param Context $context
     * @param Config $config
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        Config $config,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        parent::__construct($context, $data, $secureRenderer);
        $this->config = $config;
    }


    /**
     * Set custom template for the verification button
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Amwal_Payments::system/config/verify-merchant.phtml');

        return $this;
    }

    /**
     * Generate HTML for the verification button element
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();

        $this->addData([
            'button_label' => __($originalData['button_label'] ?? 'Verify Merchant ID'),
            'html_id' => $element->getHtmlId(),
            'api_url' => $this->getApiValidationUrl(),
        ]);

        return $this->_toHtml();
    }

    /**
     * Get the API URL for merchant validation
     *
     * @return string
     */
    private function getApiValidationUrl(): string
    {
        return rtrim($this->config->getApiUrl(), '/') . '/merchant/check/';
    }
}
