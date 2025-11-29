<?php

declare(strict_types=1);

namespace App\Address;

use App\Entity\NullablePostAddress;
use App\Entity\PostAddress;

class PostAddressFactory
{
    public function createFlexible(
        ?string $country,
        ?string $postalCode,
        ?string $cityName,
        ?string $address,
        ?string $region = null,
    ): PostAddress {
        return PostAddress::createForeignAddress($country, $postalCode, $cityName, $address, null, $region);
    }

    public static function createFromAddress(Address $address, bool $nullable = false): AddressInterface
    {
        if ($address->isFrenchAddress()) {
            if ($nullable) {
                return NullablePostAddress::createFrenchAddress(
                    $address->getAddress(),
                    $address->getCity(),
                    $address->getCityName(),
                    $address->getAdditionalAddress(),
                    $address->getRegion()
                );
            }

            return PostAddress::createFrenchAddress(
                $address->getAddress(),
                $address->getCity(),
                $address->getCityName(),
                $address->getAdditionalAddress(),
                $address->getRegion()
            );
        }

        if ($nullable) {
            return NullablePostAddress::createAddress(
                $address->getCountry(),
                $address->getPostalCode(),
                $address->getCityName(),
                $address->getAddress(),
                $address->getAdditionalAddress(),
                $address->getRegion()
            );
        }

        return PostAddress::createForeignAddress(
            $address->getCountry(),
            $address->getPostalCode(),
            $address->getCityName(),
            $address->getAddress(),
            $address->getAdditionalAddress(),
            $address->getRegion()
        );
    }
}
