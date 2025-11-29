<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\ElectedRepresentative\ZoneCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadElectedRepresentativeZoneCategoryData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (ZoneCategory::ALL as $name) {
            $category = new ZoneCategory($name);
            $manager->persist($category);
            $this->setReference('zone-category-'.mb_strtolower($name), $category);
        }

        $manager->flush();
    }
}
