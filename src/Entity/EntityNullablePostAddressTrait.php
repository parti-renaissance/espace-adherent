<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Geocoder\Coordinates;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Intl\Intl;

trait EntityNullablePostAddressTrait
{
    /**
     * @ORM\Embedded(class="NullablePostAddress", columnPrefix="address_")
     *
     * @var NullablePostAddress
     */
    protected $postAddress;

    public function getPostAddressModel(): NullablePostAddress
    {
        return $this->postAddress;
    }

    /**
     * @Algolia\Attribute(algoliaName="address")
     */
    public function getInlineFormattedAddress($locale = 'fr_FR'): string
    {
        return $this->postAddress ? $this->postAddress->getInlineFormattedAddress($locale) : '';
    }

    /**
     * @JMS\Groups({"public", "citizen_project_read"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("country")
     */
    public function getCountry(): ?string
    {
        return $this->postAddress ? $this->postAddress->getCountry() : null;
    }

    public function getCountryName(): ?string
    {
        return $this->postAddress ? Intl::getRegionBundle()->getCountryName($this->postAddress->getCountry()) : null;
    }

    /**
     * @JMS\Groups({"public", "citizen_project_read"})
     * @JMS\VirtualProperty
     */
    public function getAddress(): ?string
    {
        return $this->postAddress ? $this->postAddress->getAddress() : null;
    }

    /**
     * @JMS\Groups({"public", "citizen_project_read"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("zipCode")
     */
    public function getPostalCode(): ?string
    {
        return $this->postAddress ? $this->postAddress->getPostalCode() : null;
    }

    /**
     * @Algolia\Attribute(algoliaName="address_city")
     * @JMS\Groups({"public", "citizen_project_read"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("city")
     */
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

    /**
     * @JMS\Groups({"public", "citizen_project_read"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("latitude")
     */
    public function getLatitude(): ?float
    {
        return $this->postAddress ? $this->postAddress->getLatitude() : null;
    }

    /**
     * @JMS\Groups({"public", "citizen_project_read"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("longitude")
     */
    public function getLongitude(): ?float
    {
        return $this->postAddress ? $this->postAddress->getLongitude() : null;
    }

    /**
     * @Algolia\IndexIf
     */
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

    /**
     * @Algolia\Attribute(algoliaName="_geoloc")
     */
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
