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

    public function getInlineFormattedAddress($locale = 'fr_FR'): string
    {
        return $this->postAddress ? $this->postAddress->getInlineFormattedAddress($locale) : '';
    }

    public function getCountry(): ?string
    {
        return $this->postAddress ? $this->postAddress->getCountry() : null;
    }

    public function getCountryName(): ?string
    {
        return $this->postAddress && $this->postAddress->getCountry() ? Countries::getName($this->postAddress->getCountry()) : null;
    }

    public function getAddress(): ?string
    {
        return $this->postAddress ? $this->postAddress->getAddress() : null;
    }

    public function getPostalCode(): ?string
    {
        return $this->postAddress ? $this->postAddress->getPostalCode() : null;
    }

    public function getCityName(): ?string
    {
        return $this->postAddress ? $this->postAddress->getCityName() : null;
    }

    public function getCity(): ?string
    {
        return $this->postAddress ? $this->postAddress->getCity() : null;
    }

    public function getInseeCode(): ?string
    {
        return $this->postAddress ? $this->postAddress->getInseeCode() : null;
    }

    public function getLatitude(): ?float
    {
        return $this->postAddress ? $this->postAddress->getLatitude() : null;
    }

    public function getLongitude(): ?float
    {
        return $this->postAddress ? $this->postAddress->getLongitude() : null;
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
        return $this->postAddress ? $this->postAddress->getGeocodableHash() : null;
    }

    public function setGeocodableHash(string $hash): void
    {
        if ($this->postAddress) {
            $this->postAddress->setGeocodableHash($hash);
        }
    }
}
