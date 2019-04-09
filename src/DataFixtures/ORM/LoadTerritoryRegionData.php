<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Territory\Region;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadTerritoryRegionData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $manager->persist($region1 = new Region('Île-de-France', '11'));
        $manager->persist($region2 = new Region('Bourgogne-Franche-Comté', '27'));

        $manager->flush();

        $this->setReference('region-ile-de-france', $region1);
        $this->setReference('region-bourgogne-franche-comte', $region2);
    }
}
