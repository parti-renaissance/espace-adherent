<?php

namespace AppBundle\Entity;

use AppBundle\Address\AddressInterface;
use AppBundle\Address\GeocodableAddress;
use AppBundle\Geocoder\Coordinates;
use AppBundle\Geocoder\GeocodableInterface;
use AppBundle\Geocoder\GeoPointInterface;
use AppBundle\Intl\FranceCitiesBundle;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable
 */
class PostAddress implements AddressInterface, GeocodableInterface, GeoPointInterface
{
    const FRANCE = 'FR';

    /**
     * The address street.
     *
     * @var string|null
     *
     * @ORM\Column(length=150, nullable=true)
     */
    private $address;

    /**
     * The address zip code.
     *
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true)
     */
    private $postalCode;

    /**
     * The address city code (postal code + INSEE code).
     *
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true, name="city_insee")
     */
    private $city;

    /**
     * The address city name.
     *
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $cityName;

    /**
     * The address country code (ISO2).
     *
     * @var string
     *
     * @ORM\Column(length=2)
     */
    private $country;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     */
    private $latitude;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     */
    private $longitude;

    private function __construct(string $country, string $postalCode, string $cityName, string $street, float $latitude = null, $longitude = null)
    {
        if (empty($cityName)) {
            throw new \InvalidArgumentException('The city name cannot be empty.');
        }

        $this->country = $country;
        $this->address = $street;
        $this->postalCode = $postalCode;
        $this->cityName = $cityName;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public static function createFrenchAddress(
        string $street,
        string $cityCode,
        float $latitude = null,
        float $longitude = null
    ): self {
        list($postalCode, $inseeCode) = explode('-', $cityCode);

        $address = new self(
            self::FRANCE,
            $postalCode,
            (string) FranceCitiesBundle::getCity($postalCode, $inseeCode),
            $street,
            $latitude,
            $longitude
        );

        $address->city = sprintf('%s-%s', $postalCode, $inseeCode);

        return $address;
    }

    public static function createForeignAddress(
        string $country,
        string $zipCode,
        string $cityName,
        string $street,
        float $latitude = null,
        float $longitude = null
    ): self {
        return new self($country, $zipCode, $cityName, $street, $latitude, $longitude);
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function getCityName()
    {
        return $this->cityName;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Returns the french national INSEE code from the city code.
     *
     * @return string|null
     */
    public function getInseeCode()
    {
        $inseeCode = null;
        if ($this->city && 5 === strpos($this->city, '-')) {
            list(, $inseeCode) = explode('-', $this->city);
        }

        return $inseeCode;
    }

    public function updateCoordinates(Coordinates $coordinates)
    {
        $this->latitude = $coordinates->getLatitude();
        $this->longitude = $coordinates->getLongitude();
    }

    public function getGeocodableAddress(): string
    {
        return (string) GeocodableAddress::createFromAddress($this);
    }
}
