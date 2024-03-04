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
    private AmwalAddressId $amwalAddressId;

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
        LocaleResolver $localeResolver,
        AmwalAddressId $amwalAddressId
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
        $this->amwalAddressId = $amwalAddressId;
    }

    /**
     * @param DataObject $amwalOrderData
     * @param null|string $customerId
     * @return AddressInterface
     * @throws LocalizedException
     * @throws RuntimeException
     */
    public function execute(DataObject $amwalOrderData, ?string $customerId = null): AddressInterface
    {
        $address = null;
        if($customerId){
            /** @var AmwalAddressInterface $amwalAddress */
            $amwalAddress = $amwalOrderData->getAddressDetails();

            if ($amwalAddressId = $amwalAddress->getId()) {
                $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                    $this->amwalAddressId->getAttributeCode(),
                    $amwalAddressId
                )->addFilter(
                    'parent_id',
                    $customerId
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
                    $customerId
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
        }

        if (!$address) {
            $address = $this->createAddress($amwalOrderData, $customerId);
        }

        if (!$address) {
            throw new RuntimeException('Unable to create the address for the order.');
        }

        return $address;
    }

    /**
     * @param DataObject $amwalOrderData
     * @param null|string $customerId
     * @return AddressInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function createAddress(DataObject $amwalOrderData, ?string $customerId): AddressInterface
    {
        /** @var AmwalAddressInterface $amwalAddress */
        $amwalAddress = $amwalOrderData->getAddressDetails();

        $customerAddress = $this->addressDataFactory->create()
            ->setFirstname($this->getFirstName($amwalAddress, $amwalOrderData))
            ->setLastname($this->getLastName($amwalAddress, $amwalOrderData))
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
        if ($cityId) {
            $customerAddress->setCustomAttribute('city_id', $cityId);
        }

        if ($customerId) {
            $customerAddress->setCustomerId($customerId);
        }

        $customerAddress->setCustomAttribute(
            $this->amwalAddressId->getAttributeCode(),
            $amwalAddress->getId() ?? self::TEMPORARY_DATA_VALUE
        );

        if ($customerId) {
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
    public function isAddressMatched(AddressInterface $customerAddress, AmwalAddressInterface $amwalAddress): bool
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

        if ($this->config->shouldCombineStreetLines()) {
            $street = [implode(' ', $street)];
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
        $customerAddress->setCustomAttribute($this->amwalAddressId->getAttributeCode(), $id);
        $this->addressRepository->save($customerAddress);
    }

    /**
     * @param AddressInterface $customerAddress
     * @param DataObject $amwalOrderData
     * @return void
     * @throws LocalizedException
     */
    public function updateTmpAddressData(AddressInterface $customerAddress, DataObject $amwalOrderData): void
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

        if (class_exists('libphonenumber\PhoneNumberUtil') &&
            in_array($format, PhoneNumberFormat::getValidValues())) {
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

            if ($format === PhoneNumberFormat::COUNTRY_OPTION_VALUE) {
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

    /**
     * @param AmwalAddressInterface $amwalAddress
     * @param DataObject $amwalOrderData
     * @return string
     */
    public function getFirstName(AmwalAddressInterface $amwalAddress, DataObject $amwalOrderData): string
    {
        if ($amwalAddress->getFirstName()) {
            return $amwalAddress->getFirstName();
        }
        if ($amwalOrderData->getClientFirstName()) {
            return $amwalOrderData->getClientFirstName();
        }

        return self::TEMPORARY_DATA_VALUE;
    }

    /**
     * @param AmwalAddressInterface $amwalAddress
     * @param DataObject $amwalOrderData
     * @return string
     */
    public function getLastName(AmwalAddressInterface $amwalAddress, DataObject $amwalOrderData): ?string
    {
        if ($amwalAddress->getLastName()) {
            return $amwalAddress->getLastName();
        }
        if ($amwalOrderData->getClientLastName()) {
            return $amwalOrderData->getClientLastName();
        }

        return self::TEMPORARY_DATA_VALUE;
    }
}
