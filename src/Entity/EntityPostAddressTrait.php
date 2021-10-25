<?php

namespace App\Entity;

use App\Geocoder\Coordinates;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
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
     * @JMS\Groups({"adherent_change_diff", "committee_read"})
     * @JMS\VirtualProperty
     *
     * @SymfonySerializer\Groups({"user_profile"})
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
     * @JMS\Groups({"committee_read"})
     * @JMS\VirtualProperty
     */
    public function getAddress(): ?string
    {
        return $this->postAddress->getAddress();
    }

    /**
     * @JMS\Groups({"adherent_change_diff", "user_profile", "committee_read"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("zipCode")
     *
     * @SymfonySerializer\Groups({"user_profile", "export", "adherent_autocomplete"})
     */
    public function getPostalCode(): ?string
    {
        return $this->postAddress->getPostalCode();
    }

    /**
     * @JMS\Groups({"adherent_change_diff", "committee_read"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("city")
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
     * @JMS\Groups({"committee_read"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("latitude")
     */
    public function getLatitude(): ?float
    {
        return $this->postAddress->getLatitude();
    }

    /**
     * @JMS\Groups({"committee_read"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("longitude")
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
