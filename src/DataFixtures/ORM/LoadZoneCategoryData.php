<?php

namespace App\DataFixtures\ORM;

use App\Entity\ElectedRepresentative\ZoneCategory;
use Doctrine\Persistence\ObjectManager;

class LoadZoneCategoryData extends AbstractFixtures
{
    public function load(ObjectManager $manager)
    {
        foreach (ZoneCategory::ALL as $name) {
            $category = new ZoneCategory($name);
            $manager->persist($category);
            $this->setReference('zone-category-'.mb_strtolower($name), $category);
        }

        $manager->flush();
    }
}
