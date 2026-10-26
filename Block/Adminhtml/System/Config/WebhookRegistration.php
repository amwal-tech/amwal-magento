<?php
declare(strict_types=1);

/**
 * Amwal Payments webhook verification block
 * Shows current verification status and validation button
 */
namespace Amwal\Payments\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA\PublicKey;

/**
 * Unified webhook verification block
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
        list($scope, $scopeId) = $this->resolveScope();

        $this->addData([
            'html_id' => $element->getHtmlId(),
            'ajax_url' => $this->_urlBuilder->getUrl('amwal/webhook/validatekey'),
            'is_registered' => $this->isWebhookRegistered(),
            'fingerprint' => $this->getFingerprint(),
            'public_key_stored' => $this->isPublicKeyStored(),
            'stored_events' => $this->getStoredEvents(),
            'expected_webhook_url' => $this->getExpectedWebhookUrl(),
            'scope' => $scope,
            'scope_id' => $scopeId,
        ]);

        return $this->_toHtml();
    }

    /**
     * Check if webhook key is configured and valid
     *
     * @return bool
     */
    public function isWebhookRegistered(): bool
    {
        return $this->isPublicKeyStored();
    }

    /**
     * Get the stored API key fingerprint, or compute it from stored public key
     *
     * @return string|null
     */
    public function getFingerprint(): ?string
    {
        list($scope, $scopeId) = $this->resolveScope();
        $fingerprint = $this->_scopeConfig->getValue(
            'payment/amwal_payments/webhook/api_key_fingerprint',
            $scope,
            $scopeId
        );

        if (!empty($fingerprint)) {
            return (string) $fingerprint;
        }

        // Fallback: compute SHA-256 fingerprint from stored public key
        $publicKey = $this->getStoredPublicKey();
        if ($publicKey) {
            try {
                $rsa = PublicKeyLoader::load($publicKey);
                if ($rsa instanceof PublicKey) {
                    return hash('sha256', $rsa->toString('PKCS8'));
                }
            } catch (\Exception $e) {
                // Ignore parsing errors on fallback
            }
        }

        return null;
    }

    /**
     * Check if public key is stored
     *
     * @return bool
     */
    public function isPublicKeyStored(): bool
    {
        return !empty($this->getStoredPublicKey());
    }

    /**
     * Get decrypted stored public key
     *
     * @return string|null
     */
    public function getStoredPublicKey(): ?string
    {
        list($scope, $scopeId) = $this->resolveScope();
        $encrypted = $this->_scopeConfig->getValue(
            'payment/amwal_payments/webhook/public_key',
            $scope,
            $scopeId
        );

        if (empty($encrypted)) {
            return null;
        }

        try {
            return $this->encryptor->decrypt($encrypted);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Resolve current admin scope
     *
     * @return array [scope, scopeId]
     */
    public function resolveScope(): array
    {
        $websiteId = (int) $this->getRequest()->getParam('website', 0);
        $storeId = (int) $this->getRequest()->getParam('store', 0);

        if ($storeId) {
            return [ScopeInterface::SCOPE_STORE, $storeId];
        }
        if ($websiteId) {
            return [ScopeInterface::SCOPE_WEBSITE, $websiteId];
        }
        return [ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0];
    }

    /**
     * Get stored subscribed webhook events
     *
     * @return array
     */
    public function getStoredEvents(): array
    {
        list($scope, $scopeId) = $this->resolveScope();
        $eventsString = (string) $this->_scopeConfig->getValue(
            'payment/amwal_payments/webhook/events',
            $scope,
            $scopeId
        );

        if (empty($eventsString)) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $eventsString)));
    }

    /**
     * Get expected store webhook URL
     *
     * @return string
     */
    public function getExpectedWebhookUrl(): string
    {
        return rtrim($this->_storeManager->getStore()->getBaseUrl(), '/') . '/rest/V1/amwal/webhook';
    }

    /**
     * Get stored webhook key type (production or sandbox)
     *
     * @return string
     */
    public function getStoredKeyType(): string
    {
        list($scope, $scopeId) = $this->resolveScope();
        return (string) $this->_scopeConfig->getValue(
            'payment/amwal_payments/webhook/key_type',
            $scope,
            $scopeId
        );
    }

    /**
     * Get store environment mode (production or sandbox based on merchant_id)
     *
     * @return string
     */
    public function getStoreEnvironment(): string
    {
        list($scope, $scopeId) = $this->resolveScope();
        $merchantId = strtolower((string) $this->_scopeConfig->getValue(
            'payment/amwal_payments/merchant_id',
            $scope,
            $scopeId
        ));
        return (strpos($merchantId, 'sandbox') !== false) ? 'sandbox' : 'production';
    }
}
