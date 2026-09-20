<?php
declare(strict_types=1);

namespace Amwal\Payments\Test\Unit\Model\Config\Backend;

use Amwal\Payments\Model\Config\Backend\SecretKey;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SecretKeyTest extends TestCase
{
    private ObjectManager $objectManager;
    private MockObject|ScopeConfigInterface $scopeConfigMock;
    private MockObject|EncryptorInterface $encryptorMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->encryptorMock = $this->createMock(EncryptorInterface::class);
    }

    public function testThrowsExceptionWhenActiveAndSecretKeyEmpty(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('The Secret Key is required when Amwal Payments is enabled.');

        /** @var SecretKey $model */
        $model = $this->objectManager->getObject(SecretKey::class, [
            'config' => $this->scopeConfigMock,
            'encryptor' => $this->encryptorMock,
            'data' => [
                'groups' => [
                    'amwal_payments' => [
                        'fields' => [
                            'active' => [
                                'value' => '1'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $model->setValue('');
        $model->beforeSave();
    }

    public function testSucceedsWhenDisabledAndSecretKeyEmpty(): void
    {
        /** @var SecretKey $model */
        $model = $this->objectManager->getObject(SecretKey::class, [
            'config' => $this->scopeConfigMock,
            'encryptor' => $this->encryptorMock,
            'data' => [
                'groups' => [
                    'amwal_payments' => [
                        'fields' => [
                            'active' => [
                                'value' => '0'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $model->setValue('');
        $model->beforeSave();
        $this->assertTrue(true, 'No exception thrown when Amwal is disabled');
    }

    public function testEncryptsWhenActiveAndValidSecretKeyProvided(): void
    {
        $this->encryptorMock->expects($this->once())
            ->method('encrypt')
            ->with('amwal_secret_key_123')
            ->willReturn('encrypted_secret_key_123');

        /** @var SecretKey $model */
        $model = $this->objectManager->getObject(SecretKey::class, [
            'config' => $this->scopeConfigMock,
            'encryptor' => $this->encryptorMock,
            'data' => [
                'groups' => [
                    'amwal_payments' => [
                        'fields' => [
                            'active' => [
                                'value' => '1'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $model->setValue('amwal_secret_key_123');
        $model->beforeSave();
        $this->assertEquals('encrypted_secret_key_123', $model->getValue());
    }

    public function testPreservesOldValueWhenMaskedAsterisksSubmitted(): void
    {
        $this->scopeConfigMock->method('getValue')
            ->willReturn('previously_encrypted_key');

        /** @var SecretKey $model */
        $model = $this->objectManager->getObject(SecretKey::class, [
            'config' => $this->scopeConfigMock,
            'encryptor' => $this->encryptorMock,
            'data' => [
                'groups' => [
                    'amwal_payments' => [
                        'fields' => [
                            'active' => [
                                'value' => '1'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $model->setValue('******');
        $model->beforeSave();
        $this->assertTrue(true, 'Validation passes when existing key is preserved');
    }

    public function testThrowsExceptionWhenMaskedAsterisksSubmittedWithNoPriorKey(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('The Secret Key is required when Amwal Payments is enabled.');

        $this->scopeConfigMock->method('getValue')
            ->willReturn('');

        /** @var SecretKey $model */
        $model = $this->objectManager->getObject(SecretKey::class, [
            'config' => $this->scopeConfigMock,
            'encryptor' => $this->encryptorMock,
            'data' => [
                'groups' => [
                    'amwal_payments' => [
                        'fields' => [
                            'active' => [
                                'value' => '1'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $model->setValue('******');
        $model->beforeSave();
    }
}
