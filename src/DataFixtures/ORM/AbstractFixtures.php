<?php

namespace App\DataFixtures\ORM;

use App\Entity\Geo\Zone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractFixtures extends Fixture implements FixtureGroupInterface
{
    protected function getZoneEntity(EntityManagerInterface $manager, int $id): Zone
    {
        return $manager->getPartialReference(Zone::class, $id);
    }

    public static function getGroups(): array
    {
        return ['default'];
    }
}
