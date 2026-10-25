<?php
/**
 * Amwal Payments webhook API key creation controller
 */
namespace Amwal\Payments\Controller\Adminhtml\Webhook;

use Amwal\Payments\Model\AmwalClientFactory;
use Amwal\Payments\Model\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Controller for creating API key
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class CreateApiKey extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Amwal_Payments::config';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Curl|null $curl
     * @param Json|null $json
     * @param WriterInterface|null $configWriter
     * @param EncryptorInterface|null $encryptor
     * @param AmwalClientFactory|null $amwalClientFactory
     * @param Config|null $config
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ?Curl $curl = null,
        ?Json $json = null,
        ?WriterInterface $configWriter = null,
        ?EncryptorInterface $encryptor = null,
        ?AmwalClientFactory $amwalClientFactory = null,
        ?Config $config = null,
        ?StoreManagerInterface $storeManager = null
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute API key creation (Deprecated: delegates to ValidateKey)
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $publicKey = trim((string) $this->getRequest()->getParam('public_key'));

            if (!empty($publicKey)) {
                /** @var ValidateKey $validateController */
                $validateController = $this->_objectManager->get(ValidateKey::class);
                return $validateController->execute();
            }

            return $result->setData([
                'success' => false,
                'message' => __('Automatic webhook creation via API has been deprecated. Please configure your webhook in the Amwal Merchant Portal (Settings > Webhook Sandbox / Production), enter the Public Key, and click Validate Key.')
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
