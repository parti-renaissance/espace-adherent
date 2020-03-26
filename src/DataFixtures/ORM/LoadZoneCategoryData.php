<?php

namespace App\DataFixtures;

use AppBundle\Entity\ElectedRepresentative\ZoneCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadZoneCategoryData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        foreach (ZoneCategory::ALL as $name) {
            $category = new ZoneCategory($name);
            $manager->persist($category);
            $this->setReference('zone-category-'.mb_strtolower($category), $category);
        }

        $manager->flush();
    }
}
