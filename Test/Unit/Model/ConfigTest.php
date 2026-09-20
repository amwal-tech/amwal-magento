<?php
declare(strict_types=1);

namespace Amwal\Payments\Test\Unit\Model;

use Amwal\Payments\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private ObjectManager $objectManager;
    private MockObject|ScopeConfigInterface $scopeConfigMock;
    private Config $config;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $this->config = $this->objectManager->getObject(Config::class, [
            'scopeConfig' => $this->scopeConfigMock
        ]);
    }

    public function testIsActiveReturnsFalseWhenSecretKeyIsEmpty(): void
    {
        $this->scopeConfigMock->method('isSetFlag')->willReturnMap([
            [Config::XML_CONFIG_PATH_ACTIVE, ScopeInterface::SCOPE_WEBSITE, null, true],
            [Config::XML_CONFIG_PATH_MERCHANT_ID_VALID, ScopeInterface::SCOPE_WEBSITE, null, true]
        ]);

        $this->scopeConfigMock->method('getValue')->willReturnMap([
            [Config::XML_CONFIG_PATH_MERCHANT_ID, ScopeInterface::SCOPE_WEBSITE, null, 'merchant-id-123'],
            [Config::XML_CONFIG_PATH_SECRET_KEY, ScopeInterface::SCOPE_WEBSITE, null, '']
        ]);

        $this->assertFalse($this->config->isActive());
    }

    public function testIsActiveReturnsTrueWhenSecretKeyIsPresent(): void
    {
        $this->scopeConfigMock->method('isSetFlag')->willReturnMap([
            [Config::XML_CONFIG_PATH_ACTIVE, ScopeInterface::SCOPE_WEBSITE, null, true],
            [Config::XML_CONFIG_PATH_MERCHANT_ID_VALID, ScopeInterface::SCOPE_WEBSITE, null, true]
        ]);

        $this->scopeConfigMock->method('getValue')->willReturnMap([
            [Config::XML_CONFIG_PATH_MERCHANT_ID, ScopeInterface::SCOPE_WEBSITE, null, 'merchant-id-123'],
            [Config::XML_CONFIG_PATH_SECRET_KEY, ScopeInterface::SCOPE_WEBSITE, null, 'amwal-secret-key-xyz']
        ]);

        $this->assertTrue($this->config->isActive());
    }

    public function testIsApplePayActiveReturnsFalseWhenSecretKeyIsEmpty(): void
    {
        $this->scopeConfigMock->method('isSetFlag')->willReturnMap([
            [Config::XML_CONFIG_PATH_APPLE_PAY_ACTIVE, ScopeInterface::SCOPE_WEBSITE, null, true],
            [Config::XML_CONFIG_PATH_MERCHANT_ID_VALID, ScopeInterface::SCOPE_WEBSITE, null, true]
        ]);

        $this->scopeConfigMock->method('getValue')->willReturnMap([
            [Config::XML_CONFIG_PATH_MERCHANT_ID, ScopeInterface::SCOPE_WEBSITE, null, 'merchant-id-123'],
            [Config::XML_CONFIG_PATH_SECRET_KEY, ScopeInterface::SCOPE_WEBSITE, null, '']
        ]);

        $this->assertFalse($this->config->isApplePayActive());
    }

    public function testIsBankInstallmentsActiveReturnsFalseWhenSecretKeyIsEmpty(): void
    {
        $this->scopeConfigMock->method('isSetFlag')->willReturnMap([
            [Config::XML_CONFIG_PATH_BANK_INSTALLMENTS_ACTIVE, ScopeInterface::SCOPE_WEBSITE, null, true],
            [Config::XML_CONFIG_PATH_MERCHANT_ID_VALID, ScopeInterface::SCOPE_WEBSITE, null, true]
        ]);

        $this->scopeConfigMock->method('getValue')->willReturnMap([
            [Config::XML_CONFIG_PATH_MERCHANT_ID, ScopeInterface::SCOPE_WEBSITE, null, 'merchant-id-123'],
            [Config::XML_CONFIG_PATH_SECRET_KEY, ScopeInterface::SCOPE_WEBSITE, null, '']
        ]);

        $this->assertFalse($this->config->isBankInstallmentsActive());
    }
}
