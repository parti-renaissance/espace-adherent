<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Address\NullableAddress;
use AppBundle\Geocoder\Coordinates;
use Doctrine\ORM\Mapping as ORM;
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

    public function getCountry(): ?string
    {
        return $this->postAddress ? $this->postAddress->getCountry() : null;
    }

    public function getCountryName(): ?string
    {
        return $this->postAddress ? Intl::getRegionBundle()->getCountryName($this->postAddress->getCountry()) : null;
    }

    public function getAddress(): ?string
    {
        return $this->postAddress ? $this->postAddress->getAddress() : null;
    }

    public function getPostalCode(): ?string
    {
        return $this->postAddress ? $this->postAddress->getPostalCode() : null;
    }

    /**
     * @Algolia\Attribute(algoliaName="address_city")
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

    public function getLatitude()
    {
        return $this->postAddress ? $this->postAddress->getLatitude() : null;
    }

    public function getLongitude()
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

    public function getGeocodableAddress(): ?string
    {
        return $this->postAddress ? $this->postAddress->getGeocodableAddress() : null;
    }

    public function updateCoordinates(Coordinates $coordinates)
    {
        $this->postAddress ? $this->postAddress->updateCoordinates($coordinates) : new NullableAddress();
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
}
