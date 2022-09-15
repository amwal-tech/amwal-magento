<?php
declare(strict_types=1);

namespace Amwal\Payments\Model;

use Amwal\Payments\Api\Data\AmwalAddressInterface;
use Amwal\Payments\Setup\Patch\Data\AddCustomerAddressAmwalAddressId as AmwalAddressId;
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
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use RuntimeException;

class AddressResolver
{

    private CustomerRepositoryInterface $customerRepository;
    private Session $customerSession;
    private AddressRepositoryInterface $addressRepository;
    private AddressInterfaceFactory $addressDataFactory;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private RegionCollectionFactory $regionCollectionFactory;
    private RegionInterfaceFactory $regionFactory;
    private LoggerInterface $logger;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressDataFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RegionCollectionFactory $regionCollectionFactory,
        RegionInterfaceFactory $regionFactory,
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->regionFactory = $regionFactory;
        $this->logger = $logger;
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
            ->setFirstname($amwalOrderData->getClientFirstName())
            ->setLastname($amwalOrderData->getClientLastName())
            ->setCountryId($amwalAddress->getCountry())
            ->setCity($amwalAddress->getCity())
            ->setPostcode($amwalAddress->getPostcode())
            ->setStreet($this->getAmwalOrderStreet($amwalAddress))
            ->setTelephone($amwalOrderData->getClientPhoneNumber());

        if ($region = $this->getRegion($amwalAddress)) {
            $customerAddress->setRegion($region)
                ->setRegionId((int) $region->getRegionId());
        }

        if ($customer = $this->getCustomer()) {
            $customerAddress->setCustomerId($customer->getId());
        }

        $customerAddress->setCustomAttribute(AmwalAddressId::ATTRIBUTE_CODE, $amwalAddress->getId() ?? 'tmp');

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
        $regionCollection = $this->regionCollectionFactory->create()
            ->addCountryFilter($amwalAddress->getCountry())
            ->addRegionCodeFilter($amwalAddress->getState());

        if (!$regionCollection->count()) {
            return null;
        }

        $regionDirectory = $regionCollection->getFirstItem();

        return $this->regionFactory->create()
            ->setRegion($regionDirectory->getName())
            ->setRegionCode($regionDirectory->getCode())
            ->setRegionId($regionDirectory->getId());
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
     * @return void
     */
    private function updateTmpAddressData(AddressInterface $customerAddress, DataObject $amwalOrderData): void
    {
        $customerAddress->setFirstname($amwalOrderData->getClientFirstName());
        $customerAddress->setLastname($amwalOrderData->getClientLastName());
        $customerAddress->setTelephone($amwalOrderData->getClientPhoneNumber());
        $this->addressRepository->save($customerAddress);
    }
}