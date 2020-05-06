<?php

namespace Tests\App\Entity;

use App\Entity\LegislativeDistrictZone;
use PHPUnit\Framework\TestCase;

class LegislativeDistrictZoneTest extends TestCase
{
    public function testCreateRegionDistrictZone()
    {
        $zone = LegislativeDistrictZone::createRegionZone('1002', 'Amériques et Caraïbes');

        $this->assertSame('region', $zone->getAreaType());
        $this->assertSame('Étranger', $zone->getAreaTypeLabel());
        $this->assertSame('1002', $zone->getAreaCode());
        $this->assertSame('Amériques et Caraïbes', $zone->getName());
        $this->assertSame('1002 - Amériques et Caraïbes', (string) $zone);
    }

    /**
     * @dataProvider provideCreateDepartmentDistrictZoneData
     */
    public function testCreateDepartmentDistrictZone(
        string $expectedAreaCode,
        string $expectedAreaTypeLabel,
        string $expectedToString,
        string $areaCode,
        string $name
    ) {
        $zone = LegislativeDistrictZone::createDepartmentZone($areaCode, $name);

        $this->assertSame('departement', $zone->getAreaType());
        $this->assertSame($expectedAreaTypeLabel, $zone->getAreaTypeLabel());
        $this->assertSame($expectedAreaCode, $zone->getAreaCode());
        $this->assertSame($name, $zone->getName());
        $this->assertSame($expectedToString, (string) $zone);
    }

    public static function provideCreateDepartmentDistrictZoneData(): array
    {
        return [
            ['0001', 'Département', '01 - Ain', '0001', 'Ain'],
            ['0001', 'Département', '01 - Ain', '01', 'Ain'],
            ['002A', 'Département', '2A - Corse Sud', '2A', 'Corse Sud'],
            ['002B', 'Département', '2B - Haute Corse', '2B', 'Haute Corse'],
            ['0073', 'Département', '73 - Savoie', '73', 'Savoie'],
            ['0974', 'Outre-Mer', '974 - La Réunion', '974', 'La Réunion'],
        ];
    }

    /**
     * @dataProvider provideDefaultRankGenerationFromAreaCodeData
     */
    public function testDefaultRankGenerationFromAreaCode(string $areaCode, int $rank)
    {
        $zone = new LegislativeDistrictZone();

        // Make rank be generated automatically from area code.
        $zone->setAreaCode($areaCode);
        $this->assertSame($rank, $zone->getRank());

        // Force manual rank.
        $zone->setRank(120);
        $this->assertSame(120, $zone->getRank());

        // Rank is not regenerated when it's already set.
        $zone->setAreaCode($areaCode);
        $this->assertSame(120, $zone->getRank());
    }

    public static function provideDefaultRankGenerationFromAreaCodeData(): array
    {
        return [
            ['0001', 1],
            ['0010', 10],
            ['002A', 20],
            ['002B', 21],
            ['0021', 22],
            ['0073', 74],
            ['0092', 93],
            ['0974', 974],
            ['1002', 1002],
        ];
    }
}
