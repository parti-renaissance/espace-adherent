<?php

namespace AppBundle\Address;

use AppBundle\Entity\PostAddress;

class PostAddressFactory
{
    /**
     * @param null|string $country
     * @param null|string $postalCode
     * @param null|string $cityName
     * @param null|string $address
     * @return PostAddress
     */
    public function createFlexible(?string $country, ?string $postalCode, ?string $cityName, ?string $address): PostAddress
    {
        return PostAddress::createForeignAddress($country, $postalCode, $cityName, $address);
    }

    /**
     * @param Address $address
     * @return PostAddress
     */
    public function createFromAddress(Address $address): PostAddress
    {
        return $address->isFrenchAddress() ?
            PostAddress::createFrenchAddress(
                $address->getAddress(),
                $address->getCity()
            ) : PostAddress::createForeignAddress(
                $address->getCountry(),
                $address->getPostalCode(),
                $address->getCityName(),
                $address->getAddress()
            );
    }
}
