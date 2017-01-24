<?php

namespace AppBundle\Address;

use AppBundle\Validator\CityAssociatedToPostalCode as AssertCityAssociatedToPostalCode;
use AppBundle\Validator\FrenchCity as AssertFrenchCity;
use AppBundle\Validator\UnitedNationsCountry as AssertUnitedNationsCountry;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertCityAssociatedToPostalCode(message="common.city.invalid_postal_code")
 */
class Address
{
    const FRANCE = 'FR';

    /**
     * @Assert\NotBlank(message="common.address.required")
     * @Assert\Length(max=150, maxMessage="common.address.max_length")
     */
    private $address;

    /**
     * @Assert\NotBlank
     */
    private $postalCode;

    /**
     * @AssertFrenchCity(message="common.city.invalid")
     */
    private $city;

    private $cityName;

    /**
     * @Assert\NotBlank
     * @AssertUnitedNationsCountry(message="common.country.invalid")
     */
    private $country;

    public function __construct()
    {
        $this->country = self::FRANCE;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode)
    {
        $this->postalCode = $postalCode;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function getCityName()
    {
        return $this->cityName;
    }

    public function setCityName($cityName)
    {
        $this->cityName = $cityName;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry(string $country)
    {
        $this->country = $country;
    }

    public function isFrenchAddress()
    {
        return 'FR' === $this->country && $this->city;
    }
}
