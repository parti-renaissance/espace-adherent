<?php

namespace App\Entity;

use App\Address\AddressInterface;
use App\Address\GeocodableAddress;
use App\Geocoder\Coordinates;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Serializer\Annotation\Groups;

trait EntityAddressTrait
{
    /**
     * The address street.
     *
     * @ORM\Column(length=150, nullable=true)
     *
     * @Groups({"event_write"})
     */
    protected ?string $address = null;

    /**
     * @ORM\Column(length=150, nullable=true)
     */
    protected ?string $additionalAddress = null;

    /**
     * The address zip code.
     *
     * @ORM\Column(nullable=true)
     *
     * @Groups({"event_write"})
     */
    protected ?string $postalCode = null;

    /**
     * The address city code (postal code + INSEE code).
     *
     * @ORM\Column(length=15, nullable=true, name="city_insee")
     *
     * @Groups({"profil_read", "event_write", "event_read", "event_list_read"})
     */
    protected ?string $city = null;

    /**
     * The address city name.
     *
     * @ORM\Column(nullable=true)
     *
     * @Groups({"event_write"})
     */
    protected ?string $cityName = null;

    /**
     * The address country code (ISO2).
     *
     * @ORM\Column(length=2, nullable=true)
     *
     * @Groups({"event_write"})
     */
    protected ?string $country = null;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Groups({
     *     "event_write",
     *     "profile_read",
     *     "contact_read_after_write",
     *     "contact_update",
     * })
     */
    protected ?string $region = null;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     */
    protected ?float $latitude = null;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     */
    protected ?float $longitude = null;

    /**
     * @ORM\Column(nullable=true)
     */
    protected ?string $geocodableHash = null;

    /**
     * @Groups({"event_read", "event_list_read"})
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getAdditionalAddress(): ?string
    {
        return $this->additionalAddress;
    }

    public function setAdditionalAddress(?string $additionalAddress): void
    {
        $this->additionalAddress = $additionalAddress;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    protected function __construct(
        ?string $country,
        string $postalCode = null,
        string $cityName = null,
        string $street = null,
        string $additionalAddress = null,
        float $latitude = null,
        float $longitude = null,
        string $region = null
    ) {
        $this->country = $country;
        $this->address = $street;
        $this->additionalAddress = $additionalAddress;
        $this->postalCode = $postalCode;
        $this->cityName = $cityName;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->region = $region;
    }

    /**
     * @Groups({"event_read", "event_list_read"})
     */
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
        $this->city = $city;
    }

    /**
     * @Groups({"event_read", "event_list_read"})
     */
    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    public function setCityName(?string $cityName): void
    {
        $this->cityName = $cityName;
    }

    /**
     * @Groups({"event_read", "event_list_read"})
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getCountryName(string $locale = null): ?string
    {
        return $this->country ? Countries::getName($this->country, $locale) : null;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion($region): void
    {
        $this->region = $region;
    }

    /**
     * @Groups({"event_read", "event_list_read"})
     */
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    /**
     * @Groups({"event_read", "event_list_read"})
     */
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLatitude(?float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function setLongitude(?float $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function updateCoordinates(Coordinates $coordinates): void
    {
        $this->latitude = $coordinates->getLatitude();
        $this->longitude = $coordinates->getLongitude();
    }

    public function resetCoordinates(): void
    {
        $this->latitude = $this->longitude = null;
    }

    public function getGeocodableAddress(): string
    {
        return (string) GeocodableAddress::createFromAddress($this);
    }

    public function getGeocodableHash(): ?string
    {
        return $this->geocodableHash;
    }

    public function setGeocodableHash(string $hash): void
    {
        $this->geocodableHash = $hash;
    }

    public function hasCoordinates(): bool
    {
        return null !== $this->latitude && null !== $this->longitude;
    }

    public function equals(self $other): bool
    {
        return mb_strtolower($this->address) === mb_strtolower($other->getAddress())
            && mb_strtolower($this->cityName) === mb_strtolower($other->getCityName())
            && mb_strtolower($this->postalCode) === mb_strtolower($other->getPostalCode())
            && mb_strtolower($this->country) === mb_strtolower($other->getCountry());
    }

    /**
     * Returns the french national INSEE code from the city code.
     */
    public function getInseeCode(): ?string
    {
        $inseeCode = null;
        if ($this->city && 5 === strpos($this->city, '-')) {
            [, $inseeCode] = explode('-', $this->city);
        }

        return $inseeCode;
    }

    public function getInlineFormattedAddress(?string $locale = 'fr_FR'): string
    {
        if ($this->isEmpty()) {
            return '';
        }

        $parts[] = str_replace(',', '', $this->address);
        $parts[] = sprintf('%s %s', $this->postalCode, $this->getCityName());

        if (!$this->isFrenchAddress() && $this->country) {
            $parts[] = $this->getCountryName($locale);
        }

        return implode(', ', array_map('trim', $parts));
    }

    public function isEmpty(): bool
    {
        return empty(implode('', array_map('trim', [
            $this->address,
            $this->postalCode,
            $this->city,
            $this->country,
        ])));
    }

    protected function isFrenchAddress(): bool
    {
        return AddressInterface::FRANCE === mb_strtoupper($this->country) && $this->city;
    }
}
