<?php

namespace App\DataFixtures\ORM;

use App\Entity\Geo\Region;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractFixtures extends Fixture
{
    protected function getRegionEntity(EntityManagerInterface $manager, int $id): Region
    {
        return $manager->getPartialReference(Region::class, $id);
    }
}
