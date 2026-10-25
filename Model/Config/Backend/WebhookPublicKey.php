<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Config\Backend;

use Amwal\Payments\Model\Config;
use Magento\Config\Model\Config\Backend\Encrypted;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA\PublicKey;

class WebhookPublicKey extends Encrypted
{
    /**
     * Normalize key to standard PEM format before saving
     *
     * @return void
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = trim((string) $this->getValue());

        // If the value consists of asterisks (******), keep the old value
        if (preg_match('/^\*+$/', $value)) {
            $value = (string) $this->getOldValue();
        }

        if (!empty($value)) {
            $value = $this->normalizeToPem($value);
            $this->setValue($value);
        }

        parent::beforeSave();
    }

    /**
     * Sync fallback key fingerprint after saving
     *
     * @return $this
     */
    public function afterSave()
    {
        parent::afterSave();

        $value = (string) $this->getValue();
        if (!empty($value)) {
            try {
                $decryptedKey = $this->_encryptor->decrypt($value);
                if (!empty($decryptedKey)) {
                    $fingerprint = hash('sha256', $decryptedKey);
                    /** @var WriterInterface $configWriter */
                    $configWriter = ObjectManager::getInstance()->get(WriterInterface::class);
                    $configWriter->save(
                        Config::XML_CONFIG_PATH_WEBHOOK_API_KEY_FINGERPRINT,
                        $fingerprint,
                        $this->getScope(),
                        $this->getScopeId()
                    );
                }
            } catch (\Exception $e) {
                // Ignore fingerprint sync error on save
            }
        }

        return $this;
    }

    /**
     * Normalize public key to standard PEM format
     *
     * @param string $keyString
     * @return string
     * @throws LocalizedException
     */
    private function normalizeToPem(string $keyString): string
    {
        $clean = trim($keyString);
        if (strpos($clean, '-----BEGIN') === false) {
            $clean = "-----BEGIN PUBLIC KEY-----\n" . chunk_split($clean, 64, "\n") . "-----END PUBLIC KEY-----\n";
        }

        try {
            $rsaKey = PublicKeyLoader::load($clean);
            if (!($rsaKey instanceof PublicKey)) {
                throw new LocalizedException(__('The provided key is not a valid RSA Public Key.'));
            }
            return $rsaKey->toString('PKCS8');
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new LocalizedException(__('Invalid RSA Public Key format: %1', $e->getMessage()));
        }
    }
}
