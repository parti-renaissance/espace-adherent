<?php

namespace AppBundle\Address;

use AppBundle\Entity\NullablePostAddress;
use AppBundle\Entity\PostAddress;

class PostAddressFactory
{
    public function createFlexible(?string $country, ?string $postalCode, ?string $cityName, ?string $address): PostAddress
    {
        return PostAddress::createForeignAddress($country, $postalCode, $cityName, $address);
    }

    public function createFromAddress(Address $address): PostAddress
    {
        if ($address->isFrenchAddress()) {
            return PostAddress::createFrenchAddress(
                $address->getAddress(),
                $address->getCity()
            );
        }

        return PostAddress::createForeignAddress(
            $address->getCountry(),
            $address->getPostalCode(),
            $address->getCityName(),
            $address->getAddress()
        );
    }

    public function createFromNullableAddress(NullableAddress $address): NullablePostAddress
    {
        if ($address->isFrenchAddress()) {
            return NullablePostAddress::createFrenchAddress(
                $address->getAddress(),
                $address->getCity()
            );
        }

        return NullablePostAddress::createForeignAddress(
            $address->getCountry(),
            $address->getPostalCode(),
            $address->getCityName(),
            $address->getAddress()
        );
    }
}
