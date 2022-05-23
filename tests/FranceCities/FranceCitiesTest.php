<?php

namespace Tests\App\FranceCities;

use App\Entity\Geo\Zone;
use App\FranceCities\CityValueObject;
use App\FranceCities\FranceCities;
use Tests\App\AbstractKernelTestCase;

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
     * @dataProvider provideForCityByPostalCodeAndName
     */
    public function testGetCityByPostalCodeAndName(string $postalCode, string $name, CityValueObject $expected): void
    {
        $this->assertEquals($expected, $this->franceCities->getCityByPostalCodeAndName($postalCode, $name));
    }

    /**
     * @dataProvider provideSearchCitiesForZones
     */
    public function testSearchCitiesForZones(array $zones, string $search, array $expectedCities): void
    {
        $this->assertEquals($expectedCities, $this->franceCities->searchCitiesForZones($zones, $search));
    }

    /**
     * @dataProvider provideForCityByInseeCode
     */
    public function testGetCityByInseeCode(string $inseeCode, CityValueObject $expectedCities): void
    {
        $this->assertEquals($expectedCities, $this->franceCities->getCityByInseeCode($inseeCode));
    }

    public function providePostalCodes(): array
    {
        return [
            [
                '75001',
                [
                    CityValueObject::createFromCityArray(['name' => 'Paris 1er', 'postal_code' => ['75001'], 'insee_code' => '75101']),
                ],
            ],
            [
                '94440',
                [
                    CityValueObject::createFromCityArray(['name' => 'Marolles-en-Brie', 'postal_code' => ['94440'], 'insee_code' => '94048']),
                    CityValueObject::createFromCityArray(['name' => 'Santeny', 'postal_code' => ['94440'], 'insee_code' => '94070']),
                    CityValueObject::createFromCityArray(['name' => 'Villecresnes', 'postal_code' => ['94440'], 'insee_code' => '94075']),
                ],
            ],
        ];
    }

    public function provideForCityByPostalCodeAndName(): array
    {
        return [
            [
                '77000',
                'Melun',
                CityValueObject::createFromCityArray(['name' => 'Melun', 'postal_code' => ['77000'], 'insee_code' => '77288']),
            ],
            [
                '94440',
                'Santeny',
                CityValueObject::createFromCityArray(['name' => 'Santeny', 'postal_code' => ['94440'], 'insee_code' => '94070']),
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
                CityValueObject::createFromCityArray(['name' => 'Bois-Colombes', 'postal_code' => ['92270'], 'insee_code' => '92009']),
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
                CityValueObject::createFromCityArray(['name' => 'Melun', 'postal_code' => ['77000'], 'insee_code' => '77288']),
            ],
        ];
    }

    public function provideForCityByInseeCode(): array
    {
        return [
            [
                '94048',
                CityValueObject::createFromCityArray(['name' => 'Marolles-en-Brie', 'postal_code' => ['94440'], 'insee_code' => '94048']),
            ],
            [
                '94070',
                CityValueObject::createFromCityArray(['name' => 'Santeny', 'postal_code' => ['94440'], 'insee_code' => '94070']),
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
