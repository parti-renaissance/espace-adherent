<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Geocoder\Coordinates;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

trait EntityPostAddressTrait
{
    /**
     * @ORM\Embedded(class="PostAddress", columnPrefix="address_")
     *
     * @var PostAddress
     */
    protected $postAddress;

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

    /**
     * @JMS\Groups({"adherent_change_diff", "user_profile", "public", "committee_read", "event_read", "citizen_action_read"})
     * @JMS\VirtualProperty
     */
    public function getCountry(): ?string
    {
        return $this->postAddress->getCountry();
    }

    public function getCountryName(): ?string
    {
        return Intl::getRegionBundle()->getCountryName($this->postAddress->getCountry());
    }

    /**
     * @JMS\Groups({"committee_read", "event_read", "citizen_action_read"})
     * @JMS\VirtualProperty
     */
    public function getAddress(): ?string
    {
        return $this->postAddress->getAddress();
    }

    /**
     * @JMS\Groups({"adherent_change_diff", "user_profile", "public", "committee_read", "event_read", "citizen_action_read"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("zipCode")
     *
     * @SymfonySerializer\Groups({"export"})
     */
    public function getPostalCode(): ?string
    {
        return $this->postAddress->getPostalCode();
    }

    /**
     * @Algolia\Attribute(algoliaName="address_city")
     * @JMS\Groups({"adherent_change_diff", "committee_read", "event_read", "citizen_action_read"})
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

    public function getInseeCode()
    {
        return $this->postAddress->getInseeCode();
    }

    /**
     * @JMS\Groups({"committee_read", "event_read", "citizen_action_read"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("latitude")
     */
    public function getLatitude(): ?float
    {
        return $this->postAddress->getLatitude();
    }

    /**
     * @JMS\Groups({"committee_read", "event_read", "citizen_action_read"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("longitude")
     */
    public function getLongitude(): ?float
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

    public function updateCoordinates(Coordinates $coordinates): void
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
