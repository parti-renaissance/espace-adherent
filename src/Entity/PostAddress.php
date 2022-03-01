<?php

namespace App\Entity;

use App\Address\AddressInterface;
use App\Address\GeocodableAddress;
use App\Geocoder\Coordinates;
use App\Geocoder\GeocodableInterface;
use App\Geocoder\GeoPointInterface;
use App\Intl\FranceCitiesBundle;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * @ORM\Embeddable
 */
class PostAddress implements AddressInterface, GeocodableInterface, GeoPointInterface
{
    public const FRANCE = 'FR';

    /**
     * The address street.
     *
     * @var string|null
     *
     * @ORM\Column(length=150, nullable=true)
     *
     * @SymfonySerializer\Groups({
     *     "profile_read",
     *     "event_write",
     *     "contact_read_after_write",
     *     "contact_update"
     * })
     */
    private $address;

    /**
     * The address zip code.
     *
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true)
     *
     * @SymfonySerializer\Groups({
     *     "profile_read",
     *     "event_write",
     *     "contact_read_after_write",
     *     "contact_update"
     * })
     */
    private $postalCode;

    /**
     * The address city code (postal code + INSEE code).
     *
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true, name="city_insee")
     *
     * @SymfonySerializer\Groups({
     *     "event_write",
     *     "contact_read_after_write",
     *     "contact_update"
     * })
     */
    private $city;

    /**
     * The address city name.
     *
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @SymfonySerializer\Groups({
     *     "profile_read",
     *     "event_write",
     *     "contact_read_after_write",
     *     "contact_update"
     * })
     */
    private $cityName;

    /**
     * The address country code (ISO2).
     *
     * @var string
     *
     * @ORM\Column(length=2, nullable=true)
     *
     * @SymfonySerializer\Groups({
     *     "profile_read",
     *     "event_write",
     *     "contact_read_after_write",
     *     "contact_update"
     * })
     */
    private $country;

    /**
     * @ORM\Column(nullable=true)
     *
     * @SymfonySerializer\Groups({
     *     "profile_read",
     *     "event_write",
     *     "contact_read_after_write",
     *     "contact_update"
     * })
     */
    private $region;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     */
    private $latitude;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     */
    private $longitude;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $geocodableHash;

    private function __construct(
        ?string $country,
        ?string $postalCode = null,
        ?string $cityName = null,
        ?string $street = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $region = null
    ) {
        $this->country = $country;
        $this->address = $street;
        $this->postalCode = $postalCode;
        $this->cityName = $cityName;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->region = $region;
    }

    public static function createEmptyAddress(): self
    {
        return new self(self::FRANCE);
    }

    public static function createCountryAddress(string $country): self
    {
        return new self($country);
    }

    public static function createFrenchAddress(
        ?string $street = null,
        ?string $cityCode = null,
        ?string $region = null,
        float $latitude = null,
        float $longitude = null
    ): self {
        [$postalCode, $inseeCode] = explode('-', $cityCode);

        $address = new self(
            self::FRANCE,
            $postalCode,
            (string) FranceCitiesBundle::getCity($postalCode, $inseeCode),
            $street,
            $latitude,
            $longitude,
            $region
        );

        $address->city = sprintf('%s-%s', $postalCode, $inseeCode);

        return $address;
    }

    public static function createForeignAddress(
        string $country,
        ?string $zipCode,
        ?string $cityName,
        ?string $street,
        ?string $region = null,
        float $latitude = null,
        float $longitude = null
    ): self {
        return new self($country, $zipCode, $cityName, $street, $latitude, $longitude, $region);
    }

    /**
     * @SymfonySerializer\Groups({"event_read", "event_list_read"})
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getCountryName(): ?string
    {
        return $this->country ? Countries::getName($this->country) : null;
    }

    /**
     * @SymfonySerializer\Groups({"profile_read", "event_read", "event_list_read"})
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @SymfonySerializer\Groups({"event_read", "event_list_read"})
     */
    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    /**
     * @SymfonySerializer\Groups({"event_read", "event_list_read"})
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @SymfonySerializer\Groups({"event_read", "event_list_read"})
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * @SymfonySerializer\Groups({"event_read", "event_list_read"})
     */
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    /**
     * @SymfonySerializer\Groups({"event_read", "event_list_read"})
     */
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function setCityName(?string $cityName): void
    {
        $this->cityName = $cityName;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
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
     */
    public function getInseeCode(): ?string
    {
        $inseeCode = null;
        if ($this->city && 5 === strpos($this->city, '-')) {
            list(, $inseeCode) = explode('-', $this->city);
        }

        return $inseeCode;
    }

    public function updateCoordinates(Coordinates $coordinates): void
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
            && mb_strtolower($this->country) === mb_strtolower($other->getCountry());
    }

    public function getInlineFormattedAddress($locale = 'fr_FR'): string
    {
        $parts[] = str_replace(',', '', $this->address);
        $parts[] = sprintf('%s %s', $this->postalCode, $this->getCityName());

        if (!$this->isFrenchAddress() && $this->country) {
            $parts[] = Countries::getName($this->country, $locale);
        }

        return implode(', ', array_map('trim', $parts));
    }

    private function isFrenchAddress(): bool
    {
        return 'FR' === mb_strtoupper($this->country) && $this->city;
    }

    public function hasCoordinates(): bool
    {
        return null !== $this->latitude && null !== $this->longitude;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): void
    {
        $this->region = $region;
    }

    public function getGeocodableHash(): ?string
    {
        return $this->geocodableHash;
    }

    public function setGeocodableHash(string $hash): void
    {
        $this->geocodableHash = $hash;
    }
}
