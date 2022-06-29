<?php

namespace App\DataFixtures\ORM;

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
        string $region = null,
        float $latitude = null,
        float $longitude = null
    ): PostAddress {
        [$postalCode, $inseeCode] = explode('-', $cityCode);
        $city = $this->franceCities->getCityByInseeCode($inseeCode);

        return PostAddress::createFrenchAddress($street, $cityCode, $city ? $city->getName() : null, $region, $latitude, $longitude);
    }
}
