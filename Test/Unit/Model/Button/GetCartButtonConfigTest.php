<?php
declare(strict_types=1);

namespace Amwal\Payments\Test\Unit\Model\Button;

use PHPUnit\Framework\TestCase;
use Amwal\Payments\Model\Button\GetCartButtonConfig;
use Amwal\Payments\Api\Data\RefIdDataInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Amwal\Payments\Api\Data\AmwalButtonConfigInterface;
use Amwal\Payments\Model\Data\AmwalButtonConfig;
use Magento\Quote\Model\Quote\Address;

class GetCartButtonConfigTest extends TestCase
{
    private const FIRST_NAME = 'Tester';
    private const LAST_NAME = 'Amwal';
    private const PHONE_NUMBER = '+95512345678';
    private const EMAIL = 'test@amwal.tech';
    private const POSTCODE = '12345';
    private const COUNTRY = 'SA';
    private const CITY = "City";
    private const STATE = 'State';
    private const STREET_1 = 'Street 123';
    private const STREET_2 = '12345, Region';

    private $getCartButtonConfig;
    private $checkoutSessionMock;
    private $cartRepositoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->checkoutSessionMock = $this->createMock(CheckoutSession::class);
        $this->cartRepositoryMock = $this->createMock(CartRepositoryInterface::class);

        $this->getCartButtonConfig = $objectManager->getObject(
            GetCartButtonConfig::class,
            [
                'checkoutSession' => $this->checkoutSessionMock,
                'cartRepository' => $this->cartRepositoryMock
            ]
        );
    }

    /**
     * Test the execution of GetCartButtonConfig
     */
    public function testExecuteReturnsAmwalButtonConfigInterface(): void
    {
        $refIdDataMock = $this->createMock(RefIdDataInterface::class);
        $quoteMock = $this->createMock(Quote::class);

        $this->checkoutSessionMock->method('getQuote')->willReturn($quoteMock);

        $result = $this->getCartButtonConfig->execute($refIdDataMock);

        $this->assertInstanceOf(AmwalButtonConfigInterface::class, $result);
    }

    /**
     * Test adding regular checkout button configuration
     */
    public function testAddRegularCheckoutButtonConfig(): void
    {
        $amwalButtonConfigMock = $this->createMock(AmwalButtonConfig::class);
        $quoteMock = $this->createMock(Quote::class);

        $this->checkoutSessionMock->method('getQuote')->willReturn($quoteMock);

        $shippingAddressMock = $this->createMockAddress();
        $billingAddressMock = $this->createMockAddress();

        $quoteMock->method('getShippingAddress')->willReturn($shippingAddressMock);
        $quoteMock->method('getBillingAddress')->willReturn($billingAddressMock);

        $this->getCartButtonConfig->addRegularCheckoutButtonConfig($amwalButtonConfigMock, $quoteMock);
    }

    /**
     * Create a mock address
     *
     * @return Address
     */
    private function createMockAddress(): Address
    {
        $addressMock = $this->createMock(Address::class);

        $addressMock->method('getFirstname')->willReturn(self::FIRST_NAME);
        $addressMock->method('getLastname')->willReturn(self::LAST_NAME);
        $addressMock->method('getTelephone')->willReturn(self::PHONE_NUMBER);
        $addressMock->method('getEmail')->willReturn(self::EMAIL);
        $addressMock->method('getPostcode')->willReturn(self::POSTCODE);
        $addressMock->method('getCountryId')->willReturn(self::COUNTRY);
        $addressMock->method('getCity')->willReturn(self::CITY);
        $addressMock->method('getRegionCode')->willReturn(self::STATE);
        $addressMock->method('getStreet')->willReturn([self::STREET_1, self::STREET_2]);

        return $addressMock;
    }

}
