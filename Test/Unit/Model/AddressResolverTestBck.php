<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\Unit\Model;

use Amwal\Payments\Api\Data\AmwalAddressInterface;
use Amwal\Payments\Model\AddressResolver;
use Amwal\Payments\Model\AddressResolverFactory;
use Amwal\Payments\Model\Config;
use Amwal\Payments\Model\Data\AmwalAddress;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AddressResolverTestBck extends TestCase
{
    /**
     * @var AddressResolver
     */
    private AddressResolver $addressResolver;

    /**
     * @var DataObject
     */
    private DataObject $amwalOrderData;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $mockCustomerRepository = $this->createMock(CustomerRepositoryInterface::class);
        $mockCustomerSession = $this->createMock(Session::class);
        $mockAddressRepository = $this->createMock(AddressRepositoryInterface::class);

        $mockAddressInterface = $this->createMock(AddressInterface::class);

        $mockAddressInterface->method('setFirstname')->willReturn($mockAddressInterface);
        $mockAddressInterface->method('setLastname')->willReturn($mockAddressInterface);
        $mockAddressInterface->method('setCountryId')->willReturn($mockAddressInterface);
        $mockAddressInterface->method('setCity')->willReturn($mockAddressInterface);
        $mockAddressInterface->method('setPostcode')->willReturn($mockAddressInterface);
        $mockAddressInterface->method('setStreet')->willReturn($mockAddressInterface);

        $mockAddressDataFactory = $this->createConfiguredMock(
            AddressInterfaceFactory::class,
            [
                'create' => $mockAddressInterface
            ]
        );

        $mockSearchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $mockRegionCollectionFactory = $this->createMock(RegionCollectionFactory::class);
        $mockRegionFactory = $this->createMock(RegionInterfaceFactory::class);
        $mockConfig = $this->createMock(Config::class);
        $mockResourceConnection = $this->createMock(ResourceConnection::class);
        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockLocaleResolve = $this->createMock(LocaleResolver::class);

        $this->addressResolver = new AddressResolver(
            $mockCustomerRepository,
            $mockCustomerSession,
            $mockAddressRepository,
            $mockAddressDataFactory,
            $mockSearchCriteriaBuilder,
            $mockRegionCollectionFactory,
            $mockRegionFactory,
            $mockConfig,
            $mockResourceConnection,
            $mockLogger,
            $mockLocaleResolve
        );

        $this->amwalOrderData = $this->getMockAmwalOrderData();
    }


    public function testCreateAddress()
    {
        $address = $this->addressResolver->createAddress($this->amwalOrderData);
    }


    public function testUpdateTmpAddressData()
    {
    }

    public function testIsAddressMatched()
    {

    }

    public function testGetFirstName()
    {

    }

    public function testGetLastName()
    {

    }


}
