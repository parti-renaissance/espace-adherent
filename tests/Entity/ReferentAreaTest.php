<?php

namespace Tests\App\Entity;

use App\Entity\ReferentArea;
use PHPUnit\Framework\TestCase;

class ReferentAreaTest extends TestCase
{
    public function testCreateRegionReferentArea()
    {
        $zone = ReferentArea::createRegionZone('USA', 'Amériques et Caraïbes');

        $this->assertSame('region', $zone->getAreaType());
        $this->assertSame('Étranger', $zone->getAreaTypeLabel());
        $this->assertSame('USA', $zone->getAreaCode());
        $this->assertSame('Amériques et Caraïbes', $zone->getName());
        $this->assertSame('USA - Amériques et Caraïbes', (string) $zone);
    }

    /**
     * @dataProvider provideCreateDepartmentReferentAreaData
     */
    public function testCreateDepartmentReferentArea(
        string $expectedAreaCode,
        string $expectedAreaTypeLabel,
        string $expectedToString,
        string $areaCode,
        string $name
    ) {
        $zone = ReferentArea::createDepartmentZone($areaCode, $name);

        $this->assertSame('departement', $zone->getAreaType());
        $this->assertSame($expectedAreaTypeLabel, $zone->getAreaTypeLabel());
        $this->assertSame($expectedAreaCode, $zone->getAreaCode());
        $this->assertSame($name, $zone->getName());
        $this->assertSame($expectedToString, (string) $zone);
    }

    public static function provideCreateDepartmentReferentAreaData(): array
    {
        return [
            ['01', 'Département', '01 - Ain', '01', 'Ain'],
            ['01', 'Département', '01 - Ain', '01', 'Ain'],
            ['2A', 'Département', '2A - Corse Sud', '2A', 'Corse Sud'],
            ['2B', 'Département', '2B - Haute Corse', '2B', 'Haute Corse'],
            ['73', 'Département', '73 - Savoie', '73', 'Savoie'],
            ['974', 'Outre-Mer', '974 - La Réunion', '974', 'La Réunion'],
        ];
    }

    /**
     * @dataProvider provideCreateDistrictReferentAreaData
     */
    public function testCreateDistrictReferentArea(
        string $expectedAreaCode,
        string $expectedAreaTypeLabel,
        string $expectedToString,
        string $areaCode,
        string $name
    ) {
        $zone = ReferentArea::createDistrict($areaCode, $name);

        $this->assertSame('arrondissement', $zone->getAreaType());
        $this->assertSame($expectedAreaTypeLabel, $zone->getAreaTypeLabel());
        $this->assertSame($expectedAreaCode, $zone->getAreaCode());
        $this->assertSame($name, $zone->getName());
        $this->assertSame($expectedToString, (string) $zone);
    }

    public static function provideCreateDistrictReferentAreaData(): array
    {
        return [
            ['75016', 'Arrondissement', '75016 - Paris 16e', '75016', 'Paris 16e'],
            ['75002', 'Arrondissement', '75002 - Paris 2e', '75002', 'Paris 2e'],
            ['75003', 'Arrondissement', '75003 - Paris 3e', '75003', 'Paris 3e'],
            ['75001', 'Arrondissement', '75001 - Paris 1er', '75001', 'Paris 1er'],
        ];
    }
}
