<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\City;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCityData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist($city1 = $this->createCity(
            'Lille',
            '59350',
            ['59000'],
            'FR'
        ));
        $this->addReference('city-lille', $city1);

        $manager->persist($this->createCity(
            'Roubaix',
            '59512',
            ['59100'],
            'FR'
        ));

        $manager->persist($this->createCity(
            'Seclin',
            '59560',
            ['59113'],
            'FR',
        ));

        $manager->persist($this->createCity(
            'Roquefort-les-Pins',
            '06105',
            ['06330'],
            'FR'
        ));

        $manager->flush();
    }

    private function createCity(string $name, string $inseeCode, array $postalCodes, string $country): City
    {
        return new City($name, $inseeCode, $postalCodes, $country);
    }
}
