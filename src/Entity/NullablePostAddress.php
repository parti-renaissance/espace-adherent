<?php

namespace App\Entity;

use App\Address\Address;
use App\Address\AddressInterface;
use App\Geocoder\GeocodableInterface;
use App\Geocoder\GeoPointInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable
 */
class NullablePostAddress implements AddressInterface, GeocodableInterface, GeoPointInterface
{
    use EntityAddressTrait;

    public static function createFrenchAddress(
        ?string $street,
        string $cityCode,
        string $cityName = null,
        string $region = null,
        float $latitude = null,
        float $longitude = null
    ): self {
        [$postalCode, $inseeCode] = explode('-', $cityCode);

        $address = new self(
            Address::FRANCE,
            $postalCode,
            $cityName,
            $street,
            $latitude,
            $longitude,
            $region
        );

        $address->city = sprintf('%s-%s', $postalCode, $inseeCode);

        return $address;
    }

    public static function createAddress(
        string $country,
        ?string $zipCode,
        ?string $cityName,
        ?string $street,
        ?string $region,
        float $latitude = null,
        float $longitude = null
    ): self {
        return new self($country, $zipCode, $cityName, $street, $latitude, $longitude, $region);
    }
}
