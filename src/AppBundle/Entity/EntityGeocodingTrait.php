<?php

namespace AppBundle\Entity;

use AppBundle\Geocoder\Coordinates;
use AppBundle\Intl\FranceCitiesBundle;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Intl\Intl;

trait EntityGeocodingTrait
{
    /**
     * The address street.
     *
     * @var string|null
     *
     * @ORM\Column(length=150, nullable=true)
     */
    private $address;

    /**
     * The address zip code.
     *
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true)
     */
    private $postalCode;

    /**
     * The address city code.
     *
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true)
     */
    private $city;

    /**
     * The address country code (ISO2).
     *
     * @var string
     *
     * @ORM\Column(length=2)
     */
    private $country;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     */
    private $latitude;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     */
    private $longitude;

    public function getCountry()
    {
        return $this->country;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Returns the french national INSEE code from the city code.
     *
     * @return string
     */
    public function getInseeCode(): string
    {
        list(, $inseeCode) = explode('-', $this->city);

        return $inseeCode;
    }

    public function updateCoordinates(Coordinates $coordinates)
    {
        $this->latitude = $coordinates->getLatitude();
        $this->longitude = $coordinates->getLongitude();
    }

    public function getGeocodableAddress(): string
    {
        $address = [];
        if ($this->address) {
            $address[] = str_replace(',', '', $this->address);
        }

        if ($this->postalCode && $this->city) {
            $address[] = sprintf(
                '%s %s',
                $this->postalCode,
                FranceCitiesBundle::getCity($this->postalCode, $this->getInseeCode())
            );
        }

        $address[] = Intl::getRegionBundle()->getCountryName($this->country);

        return implode(', ', $address);
    }
}
