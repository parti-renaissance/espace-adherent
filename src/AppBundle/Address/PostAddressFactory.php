<?php

namespace AppBundle\Address;

use AppBundle\Entity\PostAddress;

class PostAddressFactory
{
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
}
