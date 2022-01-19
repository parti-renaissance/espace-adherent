<?php

namespace App\Entity;

use App\Geocoder\Coordinates;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

trait EntityPostAddressTrait
{
    /**
     * @ORM\Embedded(class="App\Entity\PostAddress", columnPrefix="address_")
     *
     * @var PostAddress
     */
    protected $postAddress;

    public function getPostAddressModel(): PostAddress
    {
        return $this->postAddress;
    }

    public function setPostAddress(PostAddress $postAddress): void
    {
        $this->postAddress = $postAddress;
    }

    public function getInlineFormattedAddress($locale = 'fr_FR'): string
    {
        return $this->postAddress ? $this->postAddress->getInlineFormattedAddress($locale) : '';
    }

    /**
     * @SymfonySerializer\Groups({"user_profile", "adherent_change_diff", "committee_sync"})
     */
    public function getCountry(): ?string
    {
        return $this->postAddress->getCountry();
    }

    public function getCountryName(): ?string
    {
        return $this->postAddress->getCountry() ? Countries::getName($this->postAddress->getCountry()) : null;
    }

    /**
     * @SymfonySerializer\Groups({"committee_sync"})
     */
    public function getAddress(): ?string
    {
        return $this->postAddress->getAddress();
    }

    /**
     * @SymfonySerializer\Groups({"adherent_change_diff", "user_profile", "export", "adherent_autocomplete"})
     */
    public function getPostalCode(): ?string
    {
        return $this->postAddress->getPostalCode();
    }

    /**
     * @SymfonySerializer\Groups({"adherent_change_diff"})
     */
    public function getCityName(): ?string
    {
        return $this->postAddress->getCityName();
    }

    public function getCity()
    {
        return $this->postAddress->getCity();
    }

    public function getInseeCode(): ?string
    {
        return $this->postAddress->getInseeCode();
    }

    /**
     * @SymfonySerializer\Groups({"committee_sync"})
     */
    public function getLatitude(): ?float
    {
        return $this->postAddress->getLatitude();
    }

    /**
     * @SymfonySerializer\Groups({"committee_sync"})
     */
    public function getLongitude(): ?float
    {
        return $this->postAddress->getLongitude();
    }

    public function isGeocoded(): bool
    {
        return $this->getLatitude() && $this->getLongitude();
    }

    public function getGeocodableAddress(): string
    {
        return $this->postAddress->getGeocodableAddress();
    }

    public function updateCoordinates(Coordinates $coordinates): void
    {
        $this->postAddress->updateCoordinates($coordinates);
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
        return $this->postAddress->getGeocodableHash();
    }

    public function setGeocodableHash(string $hash): void
    {
        $this->postAddress->setGeocodableHash($hash);
    }

    public function updatePostAddress(PostAddress $postAddress): void
    {
        if (!$this->postAddress || !$this->postAddress->equals($postAddress)) {
            $this->postAddress = $postAddress;
        }
    }
}
