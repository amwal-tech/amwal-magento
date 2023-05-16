<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Data;

use Amwal\Payments\Api\Data\AmwalAddressInterface;
use Magento\Framework\DataObject;

class AmwalAddress extends DataObject implements AmwalAddressInterface
{

    /**
     * @inheritDoc
     */
    public function getId(): ?string
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId(): ?string
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function getFirstName(): ?string
    {
        return $this->getData(self::FIRST_NAME);
    }

    /**
     * @inheritDoc
     */
    public function getLastName(): ?string
    {
        return $this->getData(self::LAST_NAME);
    }

    /**
     * @inheritDoc
     */
    public function getEmail(): ?string
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * @inheritDoc
     */
    public function getCity(): string
    {
        return $this->getData(self::CITY);
    }

    /**
     * @inheritDoc
     */
    public function getCountry(): string
    {
        return $this->getData(self::COUNTRY);
    }

    /**
     * @inheritDoc
     */
    public function getPostcode(): string
    {
        return $this->getData(self::POSTCODE);
    }

    /**
     * @inheritDoc
     */
    public function getState(): string
    {
        return $this->getData(self::STATE);
    }

    /**
     * @inheritDoc
     */
    public function getStateCode(): ?string
    {
        return $this->getData(self::STATE_CODE);
    }

    /**
     * @inheritDoc
     */
    public function getStreet1(): string
    {
        return $this->getData(self::STREET1);
    }

    /**
     * @inheritDoc
     */
    public function getStreet2(): string
    {
        return $this->getData(self::STREET2) ?? '';
    }

    /**
     * @inheritDoc
     */
    public function setId(?string $id = null): AmwalAddressInterface
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId(?string $orderId = null): AmwalAddressInterface
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritDoc
     */
    public function setFirstName(?string $firstName = null): AmwalAddressInterface
    {
        return $this->setData(self::FIRST_NAME, $firstName);
    }

    /**
     * @inheritDoc
     */
    public function setLastName(?string $lastName = null): AmwalAddressInterface
    {
        return $this->setData(self::LAST_NAME, $lastName);
    }

    /**
     * @inheritDoc
     */
    public function setEmail(?string $email = null): AmwalAddressInterface
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * @inheritDoc
     */
    public function setCity(string $city): AmwalAddressInterface
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * @inheritDoc
     */
    public function setCountry(string $country): AmwalAddressInterface
    {
        return $this->setData(self::COUNTRY, $country);
    }

    /**
     * @inheritDoc
     */
    public function setPostcode(string $postcode): AmwalAddressInterface
    {
        return $this->setData(self::POSTCODE, $postcode);
    }

    /**
     * @inheritDoc
     */
    public function setState(string $state): AmwalAddressInterface
    {
        return $this->setData(self::STATE, $state);
    }

    /**
     * @inheritDoc
     */
    public function setStateCode(?string $stateCode = null): AmwalAddressInterface
    {
        return $this->setData(self::STATE_CODE, $stateCode);
    }

    /**
     * @inheritDoc
     */
    public function setStreet1(string $street1): AmwalAddressInterface
    {
        return $this->setData(self::STREET1, $street1);
    }

    /**
     * @inheritDoc
     */
    public function setStreet2(string $street2): AmwalAddressInterface
    {
        return $this->setData(self::STREET2, $street2);
    }
}
