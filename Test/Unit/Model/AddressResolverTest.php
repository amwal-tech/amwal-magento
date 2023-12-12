<?php
declare(strict_types=1);

namespace Unit\Model;

use Amwal\Payments\Api\Data\AmwalAddressInterface;
use Amwal\Payments\Model\AddressResolver;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\Data\AmwalAddress;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Data\Address;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\ResourceModel\Region\Collection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Amwal\Payments\Model\AddressResolver
 */
class AddressResolverTest extends TestCase
{
    private const TEST_CUSTOMER_ID = 15;
    private const FIRST_NAME = 'Tester';
    private const LAST_NAME = 'Amwal';
    private const PHONE_NUMBER = '+95512345678';
    private const EMAIL = 'test@amwal.tech';
    private const AMWAL_ORDER_ID = 'c776073d-4c39-4dc8-a027-5e306b13a939';
    private const POSTCODE = '12345';
    private const COUNTRY = 'SA';
    private const CITY = "City";
    private const STATE = 'State';
    private const STREET_1 = 'Street 123';
    private const STREET_2 = '12345, Region';

    private const MOCK_ADDRESS_DATA = [
        AddressInterface::ID => null,
        AddressInterface::CUSTOMER_ID => null,
        AddressInterface::REGION => null,
        AddressInterface::REGION_ID => null,
        AddressInterface::COUNTRY_ID => self::COUNTRY,
        AddressInterface::STREET => [self::STREET_1, self::STREET_2],
        AddressInterface::COMPANY => null,
        AddressInterface::TELEPHONE => self::PHONE_NUMBER,
        AddressInterface::FAX => null,
        AddressInterface::POSTCODE => self::POSTCODE,
        AddressInterface::CITY => self::CITY,
        AddressInterface::FIRSTNAME => self::FIRST_NAME,
        AddressInterface::LASTNAME => self::LAST_NAME,
        AddressInterface::MIDDLENAME => null,
        AddressInterface::PREFIX => null,
        AddressInterface::SUFFIX => null,
        AddressInterface::VAT_ID => null,
        AddressInterface::DEFAULT_BILLING => null,
        AddressInterface::DEFAULT_SHIPPING => null,
    ];

    private const TMP_FIELDS = [
        AddressInterface::FIRSTNAME,
        AddressInterface::LASTNAME,
        AddressInterface::TELEPHONE,
    ];

    /**
     * Mock customerRepository
     *
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepository;

    /**
     * Mock customerSession
     *
     * @var Session|MockObject
     */
    private $customerSession;

    /**
     * Mock addressRepository
     *
     * @var AddressRepositoryInterface|MockObject
     */
    private $addressRepository;

    /**
     * Mock addressDataFactory
     *
     * @var AddressInterfaceFactory|MockObject
     */
    private $addressDataFactory;

    /**
     * Mock searchCriteriaBuilder
     *
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * Mock regionCollectionFactoryInstance
     *
     * @var Collection|MockObject
     */
    private $regionCollectionFactoryInstance;

    /**
     * Mock regionCollectionFactory
     *
     * @var CollectionFactory|MockObject
     */
    private $regionCollectionFactory;

    /**
     * Mock regionFactoryInstance
     *
     * @var RegionInterface|MockObject
     */
    private $regionFactoryInstance;

    /**
     * Mock regionFactory
     *
     * @var RegionInterfaceFactory|MockObject
     */
    private $regionFactory;

    /**
     * Mock config
     *
     * @var Config|MockObject
     */
    private $config;

    /**
     * Mock resourceConnection
     *
     * @var ResourceConnection|MockObject
     */
    private $resourceConnection;

    /**
     * Mock logger
     *
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * Mock localeResolver
     *
     * @var Resolver|MockObject
     */
    private $localeResolver;

    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var AddressResolver
     */
    private $testObject;

    /**
     * @var Address
     */
    private $address;

    /**
     * Main set up method
     */
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customer->expects($this->any())
            ->method('getId')
            ->willReturn(self::TEST_CUSTOMER_ID);
        $this->customer->expects($this->any())
            ->method('load')
            ->willReturnSelf();

        $this->customerFactory = $this->getMockBuilder(CustomerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->customerFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->customer);

        $this->resource = $this->getMockBuilder(\Magento\Customer\Model\ResourceModel\Address::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->address = $this->objectManager->getObject(
            Address::class,
            [
                'customerFactory' => $this->customerFactory,
                'resource' => $this->resource,
            ]
        );

        $this->customerRepository = $this->createMock(CustomerRepositoryInterface::class);
        $this->customerSession = $this->createMock(Session::class);
        $this->addressRepository = $this->createMock(AddressRepositoryInterface::class);
        $this->addressDataFactory = $this->getMockBuilder(AddressInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->addressDataFactory
            ->method('create')
            ->willReturn($this->address);
        $this->searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $this->regionCollectionFactoryInstance = $this->createMock(Collection::class);
        $this->regionCollectionFactoryInstance->method('addCountryFilter')->willReturn($this->regionCollectionFactoryInstance);
        $this->regionCollectionFactoryInstance->method('count')->willReturn(0);
        $this->regionCollectionFactory = $this->createMock(CollectionFactory::class);
        $this->regionCollectionFactory->method('create')->willReturn($this->regionCollectionFactoryInstance);
        $this->regionFactoryInstance = $this->createMock(RegionInterface::class);
        $this->regionFactory = $this->createMock(RegionInterfaceFactory::class);
        $this->regionFactory->method('create')->willReturn($this->regionFactoryInstance);
        $this->config = $this->createMock(Config::class);
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->localeResolver = $this->createMock(Resolver::class);
        $this->testObject = $this->objectManager->getObject(
            AddressResolver::class,
            [
                'customerRepository' => $this->customerRepository,
                'customerSession' => $this->customerSession,
                'addressRepository' => $this->addressRepository,
                'addressDataFactory' => $this->addressDataFactory,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'regionCollectionFactory' => $this->regionCollectionFactory,
                'regionFactory' => $this->regionFactory,
                'config' => $this->config,
                'resourceConnection' => $this->resourceConnection,
                'logger' => $this->logger,
                'localeResolver' => $this->localeResolver,
            ]
        );

        $this->setMagentoAddressData();
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testCreateAddress(): void
    {
        $expectedResult = $this->address->__toArray();
        $createdAddress = $this->testObject->createAddress(
            $this->getMockAmwalOrderData(),
            true
        );

        $this->assertEquals(
            $expectedResult,
            $createdAddress->__toArray()
        );
    }

    /**
     * @return void
     */
    public function testIsAddressMatched(): void
    {
        $result = $this->testObject->isAddressMatched(
            $this->address,
            $this->getMockAddressDetails()
        );

        $this->assertEquals(true, $result);
    }

    /**
     * @return void
     */
    public function testGetFirstName(): void
    {
        $result = $this->testObject->getFirstName($this->getMockAddressDetails(), $this->getMockAmwalOrderData());

        $this->assertEquals(
            self::FIRST_NAME,
            $result
        );
    }

    /**
     * @return void
     */
    public function testGetLastName(): void
    {
        $result = $this->testObject->getLastName($this->getMockAddressDetails(), $this->getMockAmwalOrderData());

        $this->assertEquals(
            self::LAST_NAME,
            $result
        );
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testUpdateTmpAddressData(): void
    {
        $this->setMagentoAddressData(true);
        $originalAddress = $this->address;

        $this->assertEquals(
            AddressResolver::TEMPORARY_DATA_VALUE,
            $originalAddress->getFirstname()
        );
        $this->assertEquals(
            AddressResolver::TEMPORARY_DATA_VALUE,
            $originalAddress->getLastname()
        );
        $this->assertEquals(
            AddressResolver::TEMPORARY_DATA_VALUE,
            $originalAddress->getTelephone()
        );

        $mockOrderData = $this->getMockAmwalOrderData();
        $this->testObject->updateTmpAddressData($originalAddress, $mockOrderData);

        $this->assertEquals(
            self::FIRST_NAME,
            $originalAddress->getFirstname()
        );
        $this->assertEquals(
            self::LAST_NAME,
            $originalAddress->getLastname()
        );
        $this->assertEquals(
            self::PHONE_NUMBER,
            $originalAddress->getTelephone()
        );
    }

    /**
     * @param bool $useTmp
     * @return void
     */
    private function setMagentoAddressData(bool $useTmp = false): void
    {
        foreach (self::MOCK_ADDRESS_DATA as $key => $value) {
            if ($useTmp && in_array($key, self::TMP_FIELDS, true)) {
                $this->address->setData($key, AddressResolver::TEMPORARY_DATA_VALUE);
            } else {
                $this->address->setData($key, $value);
            }
        }
    }

    /**
     * @return AmwalAddressInterface
     */
    private function getMockAddressDetails(): DataObject
    {
        return (new AmwalAddress())->addData([
            'email' => self::EMAIL,
            'first_name' => self::FIRST_NAME,
            'last_name' => self::LAST_NAME,
            'street1' => self::STREET_1,
            'street2' => self::STREET_2,
            'state' => self::STATE,
            'city' => self::CITY,
            'country' => self::COUNTRY,
            'postcode' => self::POSTCODE,
            'client_phone_number' => self::PHONE_NUMBER,
            'client_email' => self::EMAIL,
            'client_first_name' => self::FIRST_NAME,
            'client_last_name' => self::LAST_NAME,
            'orderId' => self::AMWAL_ORDER_ID
        ]);
    }

    /**
     * @return DataObject
     */
    private function getMockAmwalOrderData(): DataObject
    {
        return (new DataObject())->addData([
            'client_first_name' => self::FIRST_NAME,
            'client_last_name' => self::LAST_NAME,
            'client_phone_number' => self::PHONE_NUMBER,
            'client_email' => self::EMAIL,
            'address_details' => $this->getMockAddressDetails()
        ]);
    }
}
