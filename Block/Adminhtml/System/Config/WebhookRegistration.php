<?php
/**
 * Amwal Payments webhook registration block
 * Combines register button + registration status into one clean UI component
 */
namespace Amwal\Payments\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Unified webhook registration block: shows current status + register button
 */
class WebhookRegistration extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Amwal_Payments::system/config/webhook_registration.phtml';

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
     * Render the element
     *
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->addData([
            'html_id' => $element->getHtmlId(),
            'ajax_url' => $this->_urlBuilder->getUrl('amwal/webhook/createapikey'),
            'is_registered' => $this->isWebhookRegistered(),
            'fingerprint' => $this->getFingerprint(),
            'public_key_stored' => $this->isPublicKeyStored(),
        ]);

        return $this->_toHtml();
    }

    /**
     * Check if webhook is already registered (has fingerprint)
     *
     * @return bool
     */
    public function isWebhookRegistered(): bool
    {
        return !empty($this->getFingerprint());
    }

    /**
     * Get the stored API key fingerprint
     *
     * @return string|null
     */
    public function getFingerprint(): ?string
    {
        list($scope, $scopeId) = $this->resolveScope();
        return $this->_scopeConfig->getValue(
            'payment/amwal_payments/webhook/api_key_fingerprint',
            $scope,
            $scopeId
        );
    }

    /**
     * Check if public key is stored
     *
     * @return bool
     */
    public function isPublicKeyStored(): bool
    {
        list($scope, $scopeId) = $this->resolveScope();
        return !empty($this->_scopeConfig->getValue(
            'payment/amwal_payments/webhook/public_key',
            $scope,
            $scopeId
        ));
    }

    /**
     * Resolve current admin scope
     *
     * @return array [scope, scopeId]
     */
    private function resolveScope(): array
    {
        $websiteId = (int) $this->getRequest()->getParam('website', 0);
        $storeId = (int) $this->getRequest()->getParam('store', 0);

        if ($storeId) {
            return [ScopeInterface::SCOPE_STORE, $storeId];
        }
        if ($websiteId) {
            return [ScopeInterface::SCOPE_WEBSITE, $websiteId];
        }
        return [ScopeInterface::SCOPE_WEBSITE, 0];
    }
}

