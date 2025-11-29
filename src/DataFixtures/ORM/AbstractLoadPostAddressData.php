<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Address\AddressInterface;
use App\Entity\NullablePostAddress;
use App\Entity\PostAddress;
use App\FranceCities\FranceCities;
use Doctrine\Bundle\FixturesBundle\Fixture;

abstract class AbstractLoadPostAddressData extends Fixture
{
    private FranceCities $franceCities;

    public function __construct(FranceCities $franceCities)
    {
        $this->franceCities = $franceCities;
    }

    protected function createPostAddress(
        string $street,
        string $cityCode,
        ?string $region = null,
        ?float $latitude = null,
        ?float $longitude = null,
    ): AddressInterface {
        [, $inseeCode] = explode('-', $cityCode);
        $city = $this->franceCities->getCityByInseeCode($inseeCode);

        return PostAddress::createFrenchAddress($street, $cityCode, $city?->getName(), null, $region, $latitude, $longitude);
    }

    protected function createNullablePostAddress(
        ?string $street = null,
        ?string $cityCode = null,
        ?string $region = null,
        ?float $latitude = null,
        ?float $longitude = null,
    ): AddressInterface {
        if ($cityCode) {
            [, $inseeCode] = explode('-', $cityCode);
            $city = $this->franceCities->getCityByInseeCode($inseeCode);
        }

        return NullablePostAddress::createFrenchAddress($street, $cityCode, $city?->getName(), null, $region, $latitude, $longitude);
    }
}
