<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Geo\Zone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractFixtures extends Fixture
{
    protected function getZoneEntity(EntityManagerInterface $manager, int $id): Zone
    {
        return $manager->getPartialReference(Zone::class, $id);
    }
}
