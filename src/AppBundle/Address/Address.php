<?php

namespace AppBundle\Address;

use AppBundle\Geocoder\GeocodableInterface;
use AppBundle\Intl\FranceCitiesBundle;
use AppBundle\Validator\Address as AssertValidAddress;
use AppBundle\Validator\GeocodableAddress as AssertGeocodableAddress;
use AppBundle\Validator\UnitedNationsCountry as AssertUnitedNationsCountry;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertValidAddress
 * @AssertGeocodableAddress
 */
class Address implements AddressInterface, GeocodableInterface
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
        $parts = explode('-', $city);
        if (2 !== count($parts)) {
            throw new \InvalidArgumentException(sprintf('Invalid french city format: %s.', $city));
        }

        if (!$this->postalCode) {
            $this->setPostalCode($parts[0]);
        }

        $this->city = $city;
    }

    public function getCityName()
    {
        if ($this->cityName) {
            return $this->cityName;
        }

        if ($this->postalCode && $this->city) {
            $this->cityName = FranceCitiesBundle::getCity($this->postalCode, static::getInseeCode($this->city));
        }

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

    public static function createFromAddress(AddressInterface $other): self
    {
        $address = new self();
        $address->address = $other->getAddress();
        $address->postalCode = $other->getPostalCode();
        $address->city = $other->getCity();
        $address->cityName = $other->getCityName();
        $address->country = $other->getCountry();

        return $address;
    }

    public function getGeocodableAddress(): string
    {
        return (string) GeocodableAddress::createFromAddress($this);
    }

    /**
     * Returns the french national INSEE code from the city code.
     *
     * @return string
     */
    private static function getInseeCode(string $cityCode): string
    {
        list(, $inseeCode) = explode('-', $cityCode);

        return $inseeCode;
    }
}
