<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Config\Backend;

use Amwal\Payments\Model\Config;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA\PublicKey;

/**
 * Stores the webhook RSA public key as a normalized plain-text PEM.
 *
 * A public key is not a secret, and the admin textarea prints the raw database value,
 * so the value must not be encrypted (otherwise the admin sees ciphertext and re-saves it).
 */
class WebhookPublicKey extends Value
{
    /**
     * Normalize key to standard PEM format before saving
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = trim((string) $this->getValue());

        if (!empty($value)) {
            $this->setValue($this->normalizeToPem($value));
        }

        return parent::beforeSave();
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
                /** @var WriterInterface $configWriter */
                $configWriter = ObjectManager::getInstance()->get(WriterInterface::class);
                $configWriter->save(
                    Config::XML_CONFIG_PATH_WEBHOOK_API_KEY_FINGERPRINT,
                    hash('sha256', $value),
                    $this->getScope(),
                    $this->getScopeId()
                );
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
        $clean = $this->unwrapEncrypted(trim($keyString));
        $clean = str_replace(["\r\n", '\r\n', '\n', "\r"], "\n", $clean);

        if (strpos($clean, '-----END') !== false && strpos($clean, '-----BEGIN') === false) {
            throw new LocalizedException(
                __('Incomplete Public Key: The key is missing the "-----BEGIN PUBLIC KEY-----" header and starting lines. Please copy the entire public key from the Amwal Merchant Portal.')
            );
        }

        if (strpos($clean, '-----BEGIN') !== false && strpos($clean, '-----END') === false) {
            throw new LocalizedException(
                __('Incomplete Public Key: The key is missing the "-----END PUBLIC KEY-----" footer. Please copy the entire public key from the Amwal Merchant Portal.')
            );
        }

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

    /**
     * Unwrap values stored encrypted by earlier versions (Magento ciphertext looks like "0:3:...")
     *
     * @param string $value
     * @return string
     */
    private function unwrapEncrypted(string $value): string
    {
        if (strpos($value, '-----') !== false) {
            return $value;
        }

        /** @var EncryptorInterface $encryptor */
        $encryptor = ObjectManager::getInstance()->get(EncryptorInterface::class);
        for ($i = 0; $i < 3; $i++) {
            $decrypted = trim((string) $encryptor->decrypt($value));
            if ($decrypted === '' || $decrypted === $value) {
                break;
            }
            $value = $decrypted;
            if (strpos($value, '-----') !== false) {
                break;
            }
        }

        return $value;
    }
}
