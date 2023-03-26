<?php
declare(strict_types=1);

namespace Amwal\Payments\Model;

use Amwal\Payments\Api\Data\AmwalAddressInterface;
use Amwal\Payments\Model\Config\Source\PhoneNumberFormat;
use Amwal\Payments\Setup\Patch\Data\AddCustomerAddressAmwalAddressId as AmwalAddressId;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use RuntimeException;

class AddressResolver
{
    /**
     * This value is used during address creation when certain data is unavailable.
     */
    public const TEMPORARY_DATA_VALUE = 'undefined';

    private CustomerRepositoryInterface $customerRepository;
    private Session $customerSession;
    private AddressRepositoryInterface $addressRepository;
    private AddressInterfaceFactory $addressDataFactory;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private RegionCollectionFactory $regionCollectionFactory;
    private RegionInterfaceFactory $regionFactory;
    private Config $config;
    private ResourceConnection $resourceConnection;
    private LoggerInterface $logger;
    private LocaleResolver $localeResolver;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressDataFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RegionCollectionFactory $regionCollectionFactory,
        RegionInterfaceFactory $regionFactory,
        Config $config,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger,
        LocaleResolver $localeResolver
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->regionFactory = $regionFactory;
        $this->config = $config;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        $this->localeResolver = $localeResolver;
    }

    /**
     * @param DataObject $amwalOrderData
     * @return AddressInterface
     * @throws LocalizedException
     * @throws RuntimeException
     */
    public function execute(DataObject $amwalOrderData): AddressInterface
    {
        $address = null;

        if ($this->isGuestOrder()) {
            return $this->createAddress($amwalOrderData);
        }

        /** @var AmwalAddressInterface $amwalAddress */
        $amwalAddress = $amwalOrderData->getAddressDetails();

        if ($amwalAddressId = $amwalAddress->getId()) {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                AmwalAddressId::ATTRIBUTE_CODE,
                $amwalAddressId
            )->addFilter(
                'parent_id',
                $this->getCustomerId()
            )->create();
            $matchedAddresses = $this->addressRepository->getList($searchCriteria)->getItems();
            if ($matchedAddresses) {
                $address = reset($matchedAddresses);
                $this->updateTmpAddressData($address, $amwalOrderData);
            }
        }

        if (!$address) {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                'parent_id',
                $this->getCustomerId()
            )->create();
            $customerAddresses = $this->addressRepository->getList($searchCriteria)->getItems();
            foreach ($customerAddresses as $customerAddress) {
                if ($this->isAddressMatched($customerAddress, $amwalAddress)) {
                    if ($amwalAddressId = $amwalAddress->getId()) {
                        $this->assignAmwalAddressIdToCustomerAddress($customerAddress, $amwalAddressId);
                    }
                    if ($amwalOrderData->getClientPhoneNumber() !== self::TEMPORARY_DATA_VALUE) {
                        $this->updateTmpAddressData($customerAddress, $amwalOrderData);
                    }
                    $address = $customerAddress;
                    break;
                }
            }
        }

        if (!$address) {
            $address = $this->createAddress($amwalOrderData);
        }

        if (!$address) {
            throw new RuntimeException('Unable to create the address for the order.');
        }

        return $address;
    }

    /**
     * @return bool
     */
    private function isGuestOrder(): bool
    {
        return !$this->customerSession->isLoggedIn();
    }

    /**
     * @return CustomerInterface|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getCustomer(): ?CustomerInterface
    {
        $customerId = $this->getCustomerId();
        return $customerId ? $this->customerRepository->getById($customerId) : null;
    }

    /**
     * @return int|null
     */
    private function getCustomerId(): ?int
    {
        return (int) $this->customerSession->getCustomerId() ?: null;
    }

    /**
     * @param DataObject $amwalOrderData
     * @return AddressInterface
     * @throws LocalizedException
     */
    private function createAddress(DataObject $amwalOrderData): AddressInterface
    {
        /** @var AmwalAddressInterface $amwalAddress */
        $amwalAddress = $amwalOrderData->getAddressDetails();

        $customerAddress = $this->addressDataFactory->create()
            ->setFirstname($amwalOrderData->getClientFirstName() ?? self::TEMPORARY_DATA_VALUE)
            ->setLastname($amwalOrderData->getClientLastName() ?? self::TEMPORARY_DATA_VALUE)
            ->setCountryId($amwalAddress->getCountry())
            ->setCity($amwalAddress->getCity())
            ->setPostcode($amwalAddress->getPostcode())
            ->setStreet($this->getAmwalOrderStreet($amwalAddress));


        $phoneNumber = $this->getFormattedPhoneNumber($amwalOrderData->getClientPhoneNumber());
        $customerAddress->setTelephone($phoneNumber);

        if ($region = $this->getRegion($amwalAddress)) {
            $customerAddress->setRegion($region)
                ->setRegionId((int) $region->getRegionId());
        }

        $cityId = $this->getCityId($amwalAddress);
        if ($cityId){
            $customerAddress->setCustomAttribute('city_id', $cityId);
        }

        if ($customer = $this->getCustomer()) {
            $customerAddress->setCustomerId($customer->getId());
        }

        $customerAddress->setCustomAttribute(AmwalAddressId::ATTRIBUTE_CODE, $amwalAddress->getId() ?? self::TEMPORARY_DATA_VALUE);

        if (!$this->isGuestOrder()) {
            $customerAddress = $this->addressRepository->save($customerAddress);
        }

        return $customerAddress;
    }

    /**
     * Check if the customer address matches the Amwal address
     * @param AddressInterface $customerAddress
     * @param AmwalAddressInterface $amwalAddress
     * @return bool
     */
    private function isAddressMatched(AddressInterface $customerAddress, AmwalAddressInterface $amwalAddress): bool
    {
        if ($customerAddress->getCountryId() !== $amwalAddress->getCountry()) {
            return false;
        }

        if ($customerAddress->getCity() !== $amwalAddress->getCity()) {
            return false;
        }

        if ($customerAddress->getPostcode() !== $amwalAddress->getPostcode()) {
            return false;
        }

        if ($customerAddress->getStreet() !== $this->getAmwalOrderStreet($amwalAddress)) {
            return false;
        }

        return true;
    }

    /**
     * @param AmwalAddressInterface $amwalAddress
     * @return array
     */
    private function getAmwalOrderStreet(AmwalAddressInterface $amwalAddress): array
    {
        $street = [$amwalAddress->getStreet1()];

        if ($amwalAddress->getStreet2()) {
            $street[] = $amwalAddress->getStreet2();
        }

        return $street;
    }

    /**
     * @param AmwalAddressInterface $amwalAddress
     * @return RegionInterface|null
     */
    private function getRegion(AmwalAddressInterface $amwalAddress): ?RegionInterface
    {
        $stateCode = $amwalAddress->getStateCode();
        if (!empty($stateCode)){
            $region = $this->regionCollectionFactory->create()->getItemById($stateCode);
            return $this->regionFactory->create()
                ->setRegion($region->getName())
                ->setRegionCode($region->getCode())
                ->setRegionId($region->getId());
        }

        $countryRegionsCollection = $this->regionCollectionFactory->create()
            ->addCountryFilter($amwalAddress->getCountry());

        if (!$countryRegionsCollection->count()) {
            return null;
        }

        $regionCollection = $this->regionCollectionFactory->create()
            ->addCountryFilter($amwalAddress->getCountry())
            ->addRegionCodeFilter($amwalAddress->getState());

        if (!$regionCollection->count()) {
            $regionDirectory = $countryRegionsCollection->getFirstItem();
        } else {
            $regionDirectory = $regionCollection->getFirstItem();
        }

        return $this->regionFactory->create()
            ->setRegion($regionDirectory->getName())
            ->setRegionCode($regionDirectory->getCode())
            ->setRegionId($regionDirectory->getId());
    }

    /**
     * @param AmwalAddressInterface $amwalAddress
     * @return String|null
     * @todo This should be extracted and either offered as a separate extension, or implemented directly on the business side
     */
    private function getCityId(AmwalAddressInterface $amwalAddress): ?string
    {
        $stateCode = $amwalAddress->getStateCode();
        if (empty($stateCode)){
            return null;
        }

        $locale = $this->localeResolver->getLocale();

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('directory_country_region_city');
        $localeCityTableName = $this->resourceConnection->getTableName('directory_country_region_city_name');
        if (!$connection->isTableExists($tableName) || !$connection->isTableExists($localeCityTableName)) {
            return null;
        }

        $amwalCityName = $amwalAddress->getCity();
        $condition = $connection->quoteInto('lng.locale = ?', $locale);
        $nameMatchCondition = $connection->quoteInto('main_table.default_name = ?', $amwalCityName);
        $localeNameMatchCondition = $connection->quoteInto('lng.name = ?', $amwalCityName);

        $select = $connection->select()
            ->from(['main_table' => $tableName])
            ->joinLeft(
                ['lng' => $localeCityTableName],
                'main_table.city_id = lng.city_id AND ' . $condition,
                ['name']
            )
            ->where('main_table.country_id = ?', $amwalAddress->getCountry())
            ->where('main_table.region_id = ?', $amwalAddress->getStateCode())
            ->where($nameMatchCondition . ' OR ' . $localeNameMatchCondition);

        $data = $connection->fetchRow($select);

        return $data? $data['city_id']: null;
    }

    /**
     * @param AddressInterface $customerAddress
     * @param string $id
     * @return void
     * @throws LocalizedException
     */
    private function assignAmwalAddressIdToCustomerAddress(AddressInterface $customerAddress, string $id): void
    {
        $customerAddress->setCustomAttribute(AmwalAddressId::ATTRIBUTE_CODE, $id);
        $this->addressRepository->save($customerAddress);
    }

    /**
     * @param AddressInterface $customerAddress
     * @param DataObject $amwalOrderData
     * @return void
     * @throws LocalizedException
     */
    private function updateTmpAddressData(AddressInterface $customerAddress, DataObject $amwalOrderData): void
    {
        $customerAddress->setFirstname($amwalOrderData->getClientFirstName());
        $customerAddress->setLastname($amwalOrderData->getClientLastName());
        $customerAddress->setTelephone($this->getFormattedPhoneNumber($amwalOrderData->getClientPhoneNumber()));
        $this->addressRepository->save($customerAddress);
    }

    /**
     * @param string $rawPhoneNumber
     * @return string
     */
    private function getFormattedPhoneNumber(string $rawPhoneNumber): string
    {
        $format = $this->config->getPhoneNumberFormat();
        $formattedNumber = $rawPhoneNumber;
        if ($rawPhoneNumber === self::TEMPORARY_DATA_VALUE) {
            return $rawPhoneNumber;
        }

        if (in_array($format, PhoneNumberFormat::UTILS_LIB_FORMATS)) {
            $phoneNumberUtil = PhoneNumberUtil::getInstance();
            try {
                $phoneNumber = $phoneNumberUtil->parse($rawPhoneNumber);
            } catch (NumberParseException $e) {
                $this->logger->error(sprintf(
                    'Unable to parse phone number "%s" for formatting: %s',
                    $rawPhoneNumber,
                    $e->getMessage()
                ));
                return $rawPhoneNumber;
            }

            if ($format === PhoneNumberFormat::FORMAT_COUNTRY) {
                $country = $this->config->getPhoneNumberFormatCountry();
                if (!$country) {
                    $this->logger->error(sprintf(
                        'Unable to parse phone number "%s" for formatting. Country must be specified when country formatting is selected.',
                        $rawPhoneNumber
                    ));
                    return $rawPhoneNumber;
                }
                $formattedNumber = $phoneNumberUtil->formatOutOfCountryCallingNumber($phoneNumber, $country);
            } else {
                $formattedNumber = $phoneNumberUtil->format($phoneNumber, $format);
            }
        }

        if ($this->config->getPhoneNumberTrimWhitespace()) {
            $formattedNumber = str_replace(' ', '', $formattedNumber);
        }

        return $formattedNumber;
    }
}
