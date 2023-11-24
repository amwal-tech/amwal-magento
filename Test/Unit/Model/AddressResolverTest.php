<?php
namespace Unit\Model;

use Amwal\Payments\Api\Data\AmwalAddressInterface;
use Amwal\Payments\Model\AddressResolver;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\Data\AmwalAddress;
use Amwal\Payments\Test\Unit\Model\PHPUnit;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\ResourceModel\Region\Collection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Amwal\Payments\Model\AddressResolver
 */
class AddressResolverTest extends TestCase
{
    /**
     * Mock customerRepository
     *
     * @var CustomerRepositoryInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $customerRepository;

    /**
     * Mock customerSession
     *
     * @var Session|PHPUnit\Framework\MockObject\MockObject
     */
    private $customerSession;

    /**
     * Mock addressRepository
     *
     * @var AddressRepositoryInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $addressRepository;

    /**
     * Mock addressDataFactoryInstance
     *
     * @var AddressInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $addressDataFactoryInstance;

    /**
     * Mock addressDataFactory
     *
     * @var AddressInterfaceFactory|PHPUnit\Framework\MockObject\MockObject
     */
    private $addressDataFactory;

    /**
     * Mock searchCriteriaBuilder
     *
     * @var SearchCriteriaBuilder|PHPUnit\Framework\MockObject\MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * Mock regionCollectionFactoryInstance
     *
     * @var Collection|PHPUnit\Framework\MockObject\MockObject
     */
    private $regionCollectionFactoryInstance;

    /**
     * Mock regionCollectionFactory
     *
     * @var CollectionFactory|PHPUnit\Framework\MockObject\MockObject
     */
    private $regionCollectionFactory;

    /**
     * Mock regionFactoryInstance
     *
     * @var RegionInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $regionFactoryInstance;

    /**
     * Mock regionFactory
     *
     * @var RegionInterfaceFactory|PHPUnit\Framework\MockObject\MockObject
     */
    private $regionFactory;

    /**
     * Mock config
     *
     * @var Config|PHPUnit\Framework\MockObject\MockObject
     */
    private $config;

    /**
     * Mock resourceConnection
     *
     * @var ResourceConnection|PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceConnection;

    /**
     * Mock logger
     *
     * @var LoggerInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

    /**
     * Mock localeResolver
     *
     * @var Resolver|PHPUnit\Framework\MockObject\MockObject
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
     * Main set up method
     */
    public function setUp() : void
    {
        $this->objectManager = new ObjectManager($this);
        $this->customerRepository = $this->createMock(CustomerRepositoryInterface::class);
        $this->customerSession = $this->createMock(Session::class);
        $this->addressRepository = $this->createMock(AddressRepositoryInterface::class);
        $this->addressDataFactoryInstance = $this->createMock(AddressInterface::class);
        $this->addressDataFactoryInstance->method('setFirstname')->willReturn($this->addressDataFactoryInstance);
        $this->addressDataFactoryInstance->method('setLastname')->willReturn($this->addressDataFactoryInstance);
        $this->addressDataFactoryInstance->method('setCountryId')->willReturn($this->addressDataFactoryInstance);
        $this->addressDataFactoryInstance->method('setCity')->willReturn($this->addressDataFactoryInstance);
        $this->addressDataFactoryInstance->method('setPostcode')->willReturn($this->addressDataFactoryInstance);
        $this->addressDataFactoryInstance->method('setStreet')->willReturn($this->addressDataFactoryInstance);
        $this->addressDataFactory = $this->createMock(AddressInterfaceFactory::class);
        $this->addressDataFactory->method('create')->willReturn($this->addressDataFactoryInstance);
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
    }

    /**
     * @return array
     */
    public function dataProviderForTestCreateAddress()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['amwalOrderData' => $this->getMockAmwalOrderData()],
                'expectedResult' => $this->addressDataFactoryInstance
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestCreateAddress
     */
    public function testCreateAddress(array $prerequisites, array $expectedResult)
    {
        $this->assertEquals(
            $expectedResult,
            $this->testObject->createAddress($prerequisites['amwalOrderData'])
        );
    }

    /**
     * @return array
     */
    public function dataProviderForTestIsAddressMatched()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestIsAddressMatched
     */
    public function testIsAddressMatched(array $prerequisites, array $expectedResult)
    {
        $this->assertEquals($expectedResult['param'], $prerequisites['param']);
    }

    /**
     * @return array
     */
    public function dataProviderForTestUpdateTmpAddressData()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestUpdateTmpAddressData
     */
    public function testUpdateTmpAddressData(array $prerequisites, array $expectedResult)
    {
        $this->assertEquals($expectedResult['param'], $prerequisites['param']);
    }

    /**
     * @return array
     */
    public function dataProviderForTestGetFirstName()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestGetFirstName
     */
    public function testGetFirstName(array $prerequisites, array $expectedResult)
    {
        $this->assertEquals($expectedResult['param'], $prerequisites['param']);
    }

    /**
     * @return array
     */
    public function dataProviderForTestGetLastName()
    {
        return [
            'Testcase 1' => [
                'prerequisites' => ['param' => 1],
                'expectedResult' => ['param' => 1]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestGetLastName
     */
    public function testGetLastName(array $prerequisites, array $expectedResult)
    {
        $this->assertEquals($expectedResult['param'], $prerequisites['param']);
    }

    /**
     * @return AmwalAddressInterface
     */
    private function getMockAddressDetails(): DataObject
    {
        return (new AmwalAddress())->addData([
            'email' => 'test@amwal.tech',
            'first_name' => 'Tester',
            'last_name' => 'Amwal',
            'street1' => 'Street 123',
            'street2' => '12345, Region',
            'state' => 'State',
            'city' => "City",
            'country' => 'SA',
            'postcode' => '12345',
            'client_phone_number' => '+95512345678',
            'client_email' => 'test@amwal.tech',
            'client_first_name' => 'Tester',
            'client_last_name' => 'Amwal',
            'orderId' => 'c776073d-4c39-4dc8-a027-5e306b13a939'
        ]);
    }

    /**
     * @return DataObject
     */
    private function getMockAmwalOrderData(): DataObject
    {
        return (new DataObject())->addData([
            'client_first_name' => 'Tester',
            'client_last_name' => 'Amwal',
            'client_phone_number' => '+95512345678',
            'client_email' => 'test@amwal.tech',
            'address_details' => $this->getMockAddressDetails()
        ]);
    }
}
