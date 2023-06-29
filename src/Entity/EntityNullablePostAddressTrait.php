<?php

namespace App\Entity;

use App\Geocoder\Coordinates;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Intl\Countries;

trait EntityNullablePostAddressTrait
{
    /**
     * @ORM\Embedded(class="App\Entity\NullablePostAddress", columnPrefix="address_")
     *
     * @var NullablePostAddress
     */
    protected $postAddress;

    public function getPostAddressModel(): NullablePostAddress
    {
        return $this->postAddress;
    }

    public function setPostAddress(NullablePostAddress $postAddress): void
    {
        $this->postAddress = $postAddress;
    }

    public function getInlineFormattedAddress($locale = 'fr_FR'): string
    {
        return $this->postAddress ? $this->postAddress->getInlineFormattedAddress($locale) : '';
    }

    public function getCountry(): ?string
    {
        return $this->postAddress?->getCountry();
    }

    public function getCountryName(): ?string
    {
        return $this->postAddress && $this->postAddress->getCountry() ? Countries::getName($this->postAddress->getCountry()) : null;
    }

    public function getAddress(): ?string
    {
        return $this->postAddress?->getAddress();
    }

    public function getPostalCode(): ?string
    {
        return $this->postAddress?->getPostalCode();
    }

    public function getCityName(): ?string
    {
        return $this->postAddress?->getCityName();
    }

    public function getCity(): ?string
    {
        return $this->postAddress?->getCity();
    }

    public function getInseeCode(): ?string
    {
        return $this->postAddress?->getInseeCode();
    }

    public function getLatitude(): ?float
    {
        return $this->postAddress?->getLatitude();
    }

    public function getLongitude(): ?float
    {
        return $this->postAddress?->getLongitude();
    }

    public function isGeocoded(): bool
    {
        return $this->getLatitude() && $this->getLongitude();
    }

    public function getGeocodableAddress(): string
    {
        return $this->postAddress ? $this->postAddress->getGeocodableAddress() : '';
    }

    public function updateCoordinates(Coordinates $coordinates): void
    {
        if ($this->postAddress) {
            $this->postAddress->updateCoordinates($coordinates);
        }
    }

    public function getGeolocalisation()
    {
        return [
            'lng' => $this->getLongitude(),
            'lat' => $this->getLatitude(),
        ];
    }

    public function getGeocodableHash(): ?string
    {
        return $this->postAddress?->getGeocodableHash();
    }

    public function setGeocodableHash(string $hash): void
    {
        if ($this->postAddress) {
            $this->postAddress->setGeocodableHash($hash);
        }
    }
}
