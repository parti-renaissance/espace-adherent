<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Geocoder\Coordinates;
use Doctrine\ORM\Mapping as ORM;

trait EntityPostAddressTrait
{
    /**
     * @ORM\Embedded(class="PostAddress", columnPrefix="address_")
     *
     * @var PostAddress
     */
    private $postAddress;

    public function getPostAddressModel(): PostAddress
    {
        return $this->postAddress;
    }

    /**
     * @Algolia\Attribute(algoliaName="address")
     */
    public function getInlineFormattedAddress($locale = 'fr_FR'): string
    {
        return $this->postAddress->getInlineFormattedAddress($locale);
    }

    public function getCountry(): ?string
    {
        return $this->postAddress->getCountry();
    }

    public function getAddress(): ?string
    {
        return $this->postAddress->getAddress();
    }

    public function getPostalCode(): ?string
    {
        return $this->postAddress->getPostalCode();
    }

    public function getCityName(): ?string
    {
        return $this->postAddress->getCityName();
    }

    public function getCity()
    {
        return $this->postAddress->getCity();
    }

    public function getInseeCode()
    {
        return $this->postAddress->getInseeCode();
    }

    public function getLatitude()
    {
        return $this->postAddress->getLatitude();
    }

    public function getLongitude()
    {
        return $this->postAddress->getLongitude();
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
        return $this->postAddress->getGeocodableAddress();
    }

    public function updateCoordinates(Coordinates $coordinates)
    {
        $this->postAddress->updateCoordinates($coordinates);
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
