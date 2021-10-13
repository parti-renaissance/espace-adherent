<?php

namespace App\DataFixturesPgsql;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

abstract class AbstractPgsqlFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['pgsql'];
    }
}
