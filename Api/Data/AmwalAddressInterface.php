<?php
declare(strict_types=1);

namespace Amwal\Payments\Api\Data;

interface AmwalAddressInterface
{
    public const ID = 'id';
    public const EMAIL = 'email';
    public const CITY = 'city';
    public const COUNTRY = 'country';
    public const POSTCODE = 'postcode';
    public const STATE = 'state';
    public const STATE_CODE = 'state_code';
    public const STREET1 = 'street1';
    public const STREET2 = 'street2';

    /**
     * @return string|null
     */
    public function getId(): ?string;

    /**
     * @return string|null
     */
    public function getEmail(): ?string;

    /**
     * @return string
     */
    public function getCity(): string;

    /**
     * @return string
     */
    public function getCountry(): string;

    /**
     * @return string
     */
    public function getPostcode(): string;

    /**
     * @return string
     */
    public function getState(): string;

    /**
     * @return string|null
     */
    public function getStateCode(): ?string;

    /**
     * @return string
     */
    public function getStreet1(): string;

    /**
     * @return string
     */
    public function getStreet2(): string;

    /**
     * @param string|null $id
     * @return AmwalAddressInterface
     */
    public function setId(?string $id = null): AmwalAddressInterface;

    /**
     * @param string|null $email
     * @return AmwalAddressInterface
     */
    public function setEmail(?string $email = null): AmwalAddressInterface;
    /**
     * @param string $city
     * @return AmwalAddressInterface
     */
    public function setCity(string $city): AmwalAddressInterface;

    /**
     * @param string $country
     * @return AmwalAddressInterface
     */
    public function setCountry(string $country): AmwalAddressInterface;

    /**
     * @param string $postcode
     * @return AmwalAddressInterface
     */
    public function setPostcode(string $postcode): AmwalAddressInterface;

    /**
     * @param string $state
     * @return AmwalAddressInterface
     */
    public function setState(string $state): AmwalAddressInterface;

    /**
     * @param string|null $state
     * @return AmwalAddressInterface
     */
    public function setStateCode(?string $stateCode = null): AmwalAddressInterface;

    /**
     * @param string $street1
     * @return AmwalAddressInterface
     */
    public function setStreet1(string $street1): AmwalAddressInterface;

    /**
     * @param string $street2
     * @return AmwalAddressInterface
     */
    public function setStreet2(string $street2): AmwalAddressInterface;
}
