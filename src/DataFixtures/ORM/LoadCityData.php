<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\City;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCityData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createCity(
            'Lille',
            '59350',
            $this->getReference('municipal-manager-lille')
        ));

        $manager->persist($this->createCity(
            'Roubaix',
            '59512',
            $this->getReference('municipal-manager-roubaix')
        ));

        $manager->persist($this->createCity(
            'Seclin',
            '59560'
        ));

        $manager->persist($this->createCity(
            'Roquefort-les-Pins',
            '06105'
        ));

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
        ];
    }

    private function createCity(string $name, string $inseeCode, Adherent $municipalManager = null): City
    {
        return new City($name, $inseeCode, $municipalManager);
    }
}
