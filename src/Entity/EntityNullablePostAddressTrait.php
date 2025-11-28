<?php

declare(strict_types=1);

namespace App\Entity;

use App\Geocoder\Coordinates;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait EntityNullablePostAddressTrait
{
    /**
     * @var NullablePostAddress
     */
    #[Assert\Valid(groups: ['referral_write'])]
    #[Groups(['referral_read', 'referral_write'])]
    #[ORM\Embedded(class: NullablePostAddress::class, columnPrefix: 'address_')]
    protected $postAddress;

    public function getPostAddress(): ?NullablePostAddress
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
        $this->postAddress?->updateCoordinates($coordinates);
    }

    public function resetCoordinates(): void
    {
        $this->postAddress?->resetCoordinates();
    }

    public function getGeolocalisation(): array
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
        $this->postAddress?->setGeocodableHash($hash);
    }
}
