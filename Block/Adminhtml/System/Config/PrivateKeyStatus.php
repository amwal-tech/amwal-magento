<?php
/**
 * Amwal Payments public key status block
 */
namespace Amwal\Payments\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Block for displaying public key status in system config
 */
class PrivateKeyStatus extends Field
{
    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param Context $context
     * @param EncryptorInterface $encryptor
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->encryptor = $encryptor;
    }

    /**
     * Render the field
     *
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $websiteId = (int) $this->getRequest()->getParam('website', 0);

        $scope = ScopeInterface::SCOPE_WEBSITE;
        $scopeId = 0;

        if ($websiteId) {
            $scope = ScopeInterface::SCOPE_WEBSITE;
            $scopeId = $websiteId;
        } elseif ($storeId) {
            $scope = ScopeInterface::SCOPE_STORE;
            $scopeId = $storeId;
        }

        // Check if encrypted public key exists
        $publicKeyExists = $this->_scopeConfig->getValue(
            'payment/amwal_payments/webhook/public_key',
            $scope,
            $scopeId
        );

        if ($publicKeyExists) {
            return '<div class="message message-success">' .
                __('Public key is securely stored.') .
                '</div>';
        } else {
            return '<div class="message message-warning">' .
                __('No public key stored. Click "Register Webhook" to set up webhook integration.') .
                '</div>';
        }
    }
}
