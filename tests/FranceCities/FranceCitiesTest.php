<?php

namespace Tests\App\FranceCities;

use App\Entity\Geo\Zone;
use App\FranceCities\FranceCities;
use Tests\App\AbstractKernelTestCase;

/**
 * @group debug
 */
class FranceCitiesTest extends AbstractKernelTestCase
{
    private ?FranceCities $franceCities = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->franceCities = $this->get(FranceCities::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->franceCities = null;
    }

    /**
     * @dataProvider providePostalCodes
     */
    public function testFindCitiesByPostalCode(string $postalCode, array $expectedCities): void
    {
        $this->assertEquals($expectedCities, $this->franceCities->findCitiesByPostalCode($postalCode));
    }

    /**
     * @dataProvider provideForCityInseeCode
     */
    public function testGetCityInseeCode(string $postalCode, string $name, string $expected): void
    {
        $this->assertEquals($expected, $this->franceCities->getCityInseeCode($postalCode, $name));
    }

    /**
     * @dataProvider provideSearchCitiesForZones
     */
    public function testSearchCitiesForZones(array $tags, string $search, array $expectedCities): void
    {
        $this->assertEquals($expectedCities, $this->franceCities->searchCitiesForZones($tags, $search));
    }

    /**
     * @dataProvider provideForCityByInseeCode
     */
    public function testGetCityByInseeCode(string $inseeCode, array $expectedCities): void
    {
        $this->assertEquals($expectedCities, $this->franceCities->getCityByInseeCode($inseeCode));
    }

    /**
     * @dataProvider provideForSearchCitiesByInseeCodes
     */
    public function testSearchCitiesByInseeCodes(array $inseeCodes, array $expectedCities): void
    {
        $this->assertEquals($expectedCities, $this->franceCities->searchCitiesByInseeCodes($inseeCodes));
    }

    /**
     * @dataProvider provideCity
     */
    public function testGetCityNameByInseeCode(string $inseeCode, ?string $expected): void
    {
        $this->assertEquals($expected, $this->franceCities->getCityNameByInseeCode($inseeCode));
    }

    public function providePostalCodes(): array
    {
        return [
            [
                '75001',
                [
                    75101 => 'Paris 1er',
                ],
            ],
            [
                '94440',
                [
                    94048 => 'Marolles-en-Brie',
                    94070 => 'Santeny',
                    94075 => 'Villecresnes',
                ],
            ],
        ];
    }

    public function provideCity(): array
    {
        return [
            [
                '75101',
                'Paris 1er',
            ],
            [
                '92009',
                'Bois-Colombes',
            ],
            [
                '01001',
                null,
            ],
        ];
    }

    public function provideForCityInseeCode(): array
    {
        return [
            [
                '77000',
                'Melun',
                '77288',
            ],
            [
                '94440',
                'Santeny',
                '94070',
            ],
        ];
    }

    public function provideSearchCitiesForZones(): iterable
    {
        yield [
            [
                $this->createReferentZone('92'),
            ],
            'Bois Colom',
            [
                [
                    'name' => 'Bois-Colombes',
                    'postal_code' => ['92270'],
                    'insee_code' => '92009',
                ],
            ],
        ];
        yield [
            [
                $this->createReferentZone('77'),
            ],
            'Bois Colom',
            [],
        ];

        yield [
            [
                $this->createReferentZone('92'),
            ],
            'Melun',
            [],
        ];
        yield [
            [
                $this->createReferentZone('77'),
            ],
            'Melun',
            [
                [
                    'name' => 'Melun',
                    'postal_code' => ['77000'],
                    'insee_code' => '77288',
                ],
            ],
        ];
    }

    public function provideForCityByInseeCode(): array
    {
        return [
            [
                '94048',
                [
                    'name' => 'Marolles-en-Brie',
                    'postal_code' => ['94440'],
                    'insee_code' => '94048',
                ],
            ],
            [
                '94070',
                [
                    'name' => 'Santeny',
                    'postal_code' => ['94440'],
                    'insee_code' => '94070',
                ],
            ],
        ];
    }

    public function provideForSearchCitiesByInseeCodes(): array
    {
        return [
            [
                [
                    75102,
                    92009,
                ],
                [
                    75102 => 'Paris 2ème',
                    92009 => 'Bois-Colombes',
                ],
            ],
        ];
    }

    private function createReferentZone(string $code): Zone
    {
        $zone = $this->createMock(Zone::class);
        $zone->expects($this->any())
            ->method('getCode')
            ->willReturn($code)
        ;

        return $zone;
    }
}
