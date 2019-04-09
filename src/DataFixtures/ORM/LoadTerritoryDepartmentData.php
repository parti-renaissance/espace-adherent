<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Territory\Department;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadTerritoryDepartmentData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $manager->persist($department1 = new Department(
            $this->getReference('region-ile-de-france'),
            'Hauts-de-Seine',
            '92'
        ));
        $manager->persist($department2 = new Department(
            $this->getReference('region-ile-de-france'),
            'Yvelines',
            '78'
        ));
        $manager->persist($department3 = new Department(
            $this->getReference('region-bourgogne-franche-comte'),
            'Yonne',
            '89'
        ));

        $manager->flush();

        $this->setReference('department-hauts-de-seine', $department1);
        $this->setReference('department-yvelines', $department2);
        $this->setReference('department-yonne', $department3);
    }

    public function getDependencies()
    {
        return [
            LoadTerritoryRegionData::class,
        ];
    }
}
