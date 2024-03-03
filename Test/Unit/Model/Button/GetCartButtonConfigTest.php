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
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteIdMask;

class GetCartButtonConfigTest extends TestCase
{
    private $buttonConfigMock;
    private $buttonConfigFactoryMock;
    private $quoteIdMaskFactoryMock;
    private $checkoutSessionFactoryMock;
    private $quoteIdMaskMock;
    private $quoteMock;
    private $cartRepositoryMock;
    private $quoteRepositoryMock;
    private $checkoutSessionMock;
    private $getCartButtonConfig;

    private const FIRST_NAME = 'Tester';
    private const LAST_NAME = 'Amwal';
    private const PHONE_NUMBER = '+95512345678';
    private const EMAIL = 'test@amwal.tech';
    private const POSTCODE = '12345';
    private const COUNTRY = 'SA';
    private const CITY = "Riyadh";
    private const STATE = 'Riyadh';
    private const STREET_1 = 'Street 123';
    private const STREET_2 = '12345, Region';
    private const ALLOWED_ADDRESS_COUNTRIES = ['SA'];
    private const ALLOWED_ADDRESS_CITIES = ['SA' => ['1110' => ['Riyadh'], '1111' => ['Dammam']]];
    private const ALLOWED_ADDRESS_STATES = ['SA' => ['1111' => ['Dammam'], '1110' => ['Riyadh']]];
    private const CART_ID = 'vyO7NEqZbs1Rv6Z7NLewdlLpC0qufkmJ';
    private const QUOTE_ID = 1;
    private const AMOUNT = 100.00;

    private const INITIAL_ADDRESS = [
        'city' => self::CITY,
        'state' => self::STATE,
        'postcode' => self::POSTCODE,
        'country' => self::COUNTRY,
        'street1' => self::STREET_1,
        'street2' => self::STREET_2,
        'email' => self::EMAIL
    ];

    private const MOCK_BUTTON_CONFIG_DATA = [
        'addressRequired' => false,
        'enablePrePayTrigger' => true,
        'enablePreCheckoutTrigger' => false,
        'initialAddress' => self::INITIAL_ADDRESS,
        'initialEmail' => self::EMAIL,
        'initialPhone' => self::PHONE_NUMBER,
        'initialFirstName' => self::FIRST_NAME,
        'initialLastName' => self::LAST_NAME,
        'allowedAddressCountries' => self::ALLOWED_ADDRESS_COUNTRIES,
        'allowedAddressCities' => self::ALLOWED_ADDRESS_CITIES,
        'allowedAddressStates' => self::ALLOWED_ADDRESS_STATES,
        'cartId' => self::CART_ID,
        'amount' => self::AMOUNT,
        'showDiscountRibbon' => false,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = new ObjectManager($this);

        $this->checkoutSessionMock = $this->createMock(CheckoutSession::class);
        $this->cartRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->buttonConfigMock = $this->createMock(AmwalButtonConfigInterface::class);

        $this->getCartButtonConfig = $objectManager->getObject(
            GetCartButtonConfig::class,
            [
                'checkoutSession' => $this->checkoutSessionMock,
                'cartRepository' => $this->cartRepositoryMock,
                'buttonConfig' => $this->buttonConfigMock
            ]
        );

        $this->setButtonConfigData();
    }

    /**
     * Test adding regular checkout button configuration
     */
    public function testAddRegularCheckoutButtonConfig(): void
    {
        $amwalButtonConfigMock = $this->createMock(AmwalButtonConfig::class);
        $quoteMock = $this->createMock(Quote::class);

        $this->checkoutSessionMock->method('getQuote')->willReturn($quoteMock);
        $quoteMock->method('getShippingAddress')->willReturn($this->createMockAddress());
        $quoteMock->method('getBillingAddress')->willReturn($this->createMockAddress());

        $this->getCartButtonConfig->addRegularCheckoutButtonConfig($amwalButtonConfigMock, $quoteMock);

        // Assert outcomes
        $this->assertEquals(json_encode(self::INITIAL_ADDRESS), $this->buttonConfigMock->getInitialAddress());
        $this->assertEquals(self::EMAIL, $this->buttonConfigMock->getInitialEmail());
        $this->assertEquals(self::PHONE_NUMBER, $this->buttonConfigMock->getInitialPhone());
        $this->assertEquals(self::FIRST_NAME, $this->buttonConfigMock->getInitialFirstName());
        $this->assertEquals(self::LAST_NAME, $this->buttonConfigMock->getInitialLastName());
        $this->assertEquals(false, $this->buttonConfigMock->getAddressRequired());
        $this->assertEquals(true, $this->buttonConfigMock->getEnablePrePayTrigger());
        $this->assertEquals(false, $this->buttonConfigMock->getEnablePreCheckoutTrigger());
        $this->assertEquals(json_encode(self::ALLOWED_ADDRESS_CITIES, JSON_FORCE_OBJECT), $this->buttonConfigMock->getAllowedAddressCities());
        $this->assertEquals(json_encode(self::ALLOWED_ADDRESS_STATES, JSON_FORCE_OBJECT), $this->buttonConfigMock->getAllowedAddressStates());
        $this->assertEquals(self::ALLOWED_ADDRESS_COUNTRIES, $this->buttonConfigMock->getAllowedAddressCountries());
        $this->assertEquals(self::CART_ID, $this->buttonConfigMock->getCartId());
        $this->assertEquals(self::AMOUNT, $this->buttonConfigMock->getAmount());
    }

    /**
     * Test getting discount amount
     */
    public function testGetDiscountAmount(): void
    {
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quoteMock->method('getShippingAddress')->willReturn($this->createMockAddress());
        $this->assertEquals(0, $this->getCartButtonConfig->getDiscountAmount($quoteMock, $this->buttonConfigMock, null));
    }

    /**
     * Test getting allowed address countries
     */
    public function testGetCityCodes(): void
    {
        $this->assertEquals([], $this->getCartButtonConfig->getCityCodes());
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

    /**
     * Test adding generic button configuration
     */
    private function setButtonConfigData(bool $useTmp = false): void
    {
        foreach (self::MOCK_BUTTON_CONFIG_DATA as $key => $value) {
            if (in_array($key, ['allowedAddressCities', 'allowedAddressStates', 'initialAddress'], true)) {
                $value = json_encode($value, JSON_FORCE_OBJECT);
            }
            $this->buttonConfigMock->method('get' . ucfirst($key))->willReturn($value);
        }
    }
}
