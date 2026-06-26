<?php

declare(strict_types=1);

namespace Tests\App\Unit\Entity;

use App\Entity\Action\Action;
use App\Entity\Geo\Zone;
use PHPUnit\Framework\TestCase;

class EntityZoneTraitTest extends TestCase
{
    public function testGetCityOrBoroughZonesKeepsCityAndBoroughOnly(): void
    {
        $city = new Zone(Zone::CITY, '77288', 'Melun');
        $borough = new Zone(Zone::BOROUGH, '75108', 'Paris 8e');
        $department = new Zone(Zone::DEPARTMENT, '75', 'Paris');

        $entity = new Action();
        $entity->addZone($city);
        $entity->addZone($borough);
        $entity->addZone($department);

        $zones = $entity->getCityOrBoroughZones();

        self::assertContains($city, $zones);
        self::assertContains($borough, $zones);
        self::assertNotContains($department, $zones);
        self::assertCount(2, $zones);
    }
}
