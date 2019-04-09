<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Territory\City;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadTerritoryCityData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $manager->persist(new City(
            $this->getReference('department-hauts-de-seine'),
            'Bois-Colombes',
            '92270'
        ));
        $manager->persist(new City(
            $this->getReference('department-hauts-de-seine'),
            'Saint-Ouen',
            '93400'
        ));
        $manager->persist(new City(
            $this->getReference('department-yonne'),
            'Fleury-La-Vallee',
            '89113'
        ));

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadTerritoryDepartmentData::class,
        ];
    }
}
