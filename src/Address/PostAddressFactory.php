<?php

namespace App\Address;

use App\Entity\Geo\Zone;
use App\Entity\NullablePostAddress;
use App\Entity\PostAddress;

class PostAddressFactory
{
    public function createFlexible(
        ?string $country,
        ?string $postalCode,
        ?string $cityName,
        ?string $address,
        ?string $region = null
    ): PostAddress {
        return PostAddress::createForeignAddress($country, $postalCode, $cityName, $address, $region);
    }

    public function createFromZone(Zone $zone): PostAddress
    {
        if ($zone->isCountry()) {
            return PostAddress::createCountryAddress($zone->getCode());
        }

        $address = PostAddress::createEmptyAddress();

        if ($zone->isCity() || $zone->isBorough()) {
            if ($zone->isCity()) {
                $address->setCity($zone->getCode());
                $address->setCityName($zone->getName());
            }

            return $address;
        }

        return $address;
    }

    public function createFromAddress(Address $address): PostAddress
    {
        if ($address->isFrenchAddress()) {
            return PostAddress::createFrenchAddress(
                $address->getAddress(),
                $address->getCity(),
                $address->getRegion()
            );
        }

        return PostAddress::createForeignAddress(
            $address->getCountry(),
            $address->getPostalCode(),
            $address->getCityName(),
            $address->getAddress(),
            $address->getRegion()
        );
    }

    public function createFromNullableAddress(NullableAddress $address): NullablePostAddress
    {
        if ($address->isFrenchAddress()) {
            return NullablePostAddress::createFrenchAddress(
                $address->getAddress(),
                $address->getCity(),
                $address->getRegion()
            );
        }

        return NullablePostAddress::createForeignAddress(
            $address->getCountry(),
            $address->getPostalCode(),
            $address->getCityName(),
            $address->getAddress(),
            $address->getRegion()
        );
    }
}
