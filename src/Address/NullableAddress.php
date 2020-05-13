<?php

namespace App\Address;

use App\Geocoder\GeocodableInterface;
use App\Intl\FranceCitiesBundle;
use App\Validator\Address as AssertValidAddress;
use App\Validator\GeocodableAddress as AssertGeocodableAddress;
use App\Validator\UnitedNationsCountry as AssertUnitedNationsCountry;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertValidAddress
 * @AssertGeocodableAddress
 */
class NullableAddress implements AddressInterface, GeocodableInterface
{
    const FRANCE = 'FR';

    /**
     * @Assert\Length(max=150, maxMessage="common.address.max_length")
     */
    private $address;

    /**
     * @Assert\NotBlank(message="common.postal_code.not_blank")
     * @Assert\Length(max=15)
     */
    private $postalCode;

    /**
     * @Assert\Length(max=15)
     */
    private $city;

    /**
     * @Assert\Length(max=255)
     */
    private $cityName;

    /**
     * @Assert\NotBlank
     * @AssertUnitedNationsCountry(message="common.country.invalid")
     */
    private $country;

    /**
     * @Assert\Length(max=255)
     */
    private $region;

    public function __construct()
    {
        $this->country = self::FRANCE;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        if ($city) {
            $parts = explode('-', $city);
            if (2 !== \count($parts)) {
                throw new \InvalidArgumentException(sprintf('Invalid french city format: %s.', $city));
            }

            if (!$this->postalCode) {
                $this->setPostalCode($parts[0]);
            }
        }

        $this->city = $city;
    }

    public function getCityName(): ?string
    {
        if ($this->cityName) {
            return $this->cityName;
        }

        if ($this->postalCode && $this->city) {
            $this->cityName = FranceCitiesBundle::getCity($this->postalCode, static::getInseeCode($this->city));
        }

        return $this->cityName;
    }

    public function setCityName(?string $cityName): void
    {
        $this->cityName = $cityName;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function isFrenchAddress(): bool
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
        $address->region = $other->getRegion();

        return $address;
    }

    public function getGeocodableAddress(): string
    {
        return (string) GeocodableAddress::createFromAddress($this);
    }

    /**
     * Returns the french national INSEE code from the city code.
     */
    private static function getInseeCode(string $cityCode): string
    {
        list(, $inseeCode) = explode('-', $cityCode);

        return $inseeCode;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): void
    {
        $this->region = $region;
    }
}
