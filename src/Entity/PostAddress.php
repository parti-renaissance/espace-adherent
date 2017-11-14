<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Address\AddressInterface;
use AppBundle\Address\GeocodableAddress;
use AppBundle\Geocoder\Coordinates;
use AppBundle\Geocoder\GeocodableInterface;
use AppBundle\Geocoder\GeoPointInterface;
use AppBundle\Intl\FranceCitiesBundle;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Intl\Intl;

/**
 * @ORM\Embeddable
 *
 * @Algolia\Index(autoIndex=false)
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
     *
     * @Algolia\Attribute
     */
    private $address;

    /**
     * The address zip code.
     *
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true)
     *
     * @Algolia\Attribute
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
     *
     * @Algolia\Attribute
     */
    private $cityName;

    /**
     * The address country code (ISO2).
     *
     * @var string
     *
     * @ORM\Column(length=2, nullable=true)
     *
     * @Algolia\Attribute
     */
    private $country;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     *
     * @Algolia\Attribute
     */
    private $latitude;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     *
     * @Algolia\Attribute
     */
    private $longitude;

    private function __construct(?string $country, string $postalCode, string $cityName = null, string $street = null, float $latitude = null, $longitude = null)
    {
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
        ?string $country,
        ?string $zipCode,
        ?string $cityName,
        ?string $street,
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

    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
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

    public function equals(self $other): bool
    {
        return mb_strtolower($this->address) === mb_strtolower($other->getAddress())
               && mb_strtolower($this->cityName) === mb_strtolower($other->getCityName())
               && mb_strtolower($this->postalCode) === mb_strtolower($other->getPostalCode())
               && mb_strtolower($this->country) === mb_strtolower($other->getCountry())
        ;
    }

    public function getInlineFormattedAddress($locale = 'fr_FR'): string
    {
        $parts[] = str_replace(',', '', $this->address);
        $parts[] = sprintf('%s %s', $this->postalCode, $this->getCityName());

        if (!$this->isFrenchAddress()) {
            $parts[] = Intl::getRegionBundle()->getCountryName($this->country, $locale);
        }

        return implode(', ', array_map('trim', $parts));
    }

    private function isFrenchAddress(): bool
    {
        return 'FR' === mb_strtoupper($this->country) && $this->city;
    }
}
