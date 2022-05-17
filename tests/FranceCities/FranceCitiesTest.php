<?php

namespace Tests\App\FranceCities;

use App\Entity\ReferentTag;
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
    public function testGetPostalCodeCities(string $postalCode, array $expectedCities): void
    {
        $this->assertEquals($expectedCities, $this->franceCities->getPostalCodeCities($postalCode));
    }

    /**
     * @dataProvider provideCity
     */
    public function testGetCity(string $postalCode, string $inseeCode, string $expectedCity): void
    {
        $this->assertEquals($expectedCity, $this->franceCities->getCity($postalCode, $inseeCode));
    }

    /**
     * @dataProvider provideForCityInseeCode
     */
    public function testGetCityInseeCode(string $postalCode, string $name, string $expected): void
    {
        $this->assertEquals($expected, $this->franceCities->getCityInseeCode($postalCode, $name));
    }

    /**
     * @dataProvider provideSearchCitiesForTags
     */
    public function testSearchCitiesForTags(array $tags, string $search, array $expectedCities): void
    {
        $this->assertEquals($expectedCities, $this->franceCities->searchCitiesForTags($tags, $search));
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
     * @group debug
     */
    public function testGetCityNameByInseeCode(): void
    {
        $this->assertEquals('Paris 1er', $this->franceCities->getCityNameByInseeCode('75101'));
        $this->assertEquals(null, $this->franceCities->getCityNameByInseeCode('74101'));
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
                '75001',
                '75101',
                'Paris 1er',
            ],
            [
                '92270',
                '92009',
                'Bois-Colombes',
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

    public function provideSearchCitiesForTags(): iterable
    {
        yield [
            [
                $this->createReferentTag('92', true, false),
            ],
            'Bois Colom',
            [
                [
                    'name' => 'Bois-Colombes',
                    'postal_code' => '92270',
                    'insee_code' => '92009',
                ],
            ],
        ];
        yield [
            [
                $this->createReferentTag('77', true, false),
            ],
            'Bois Colom',
            [],
        ];

        yield [
            [
                $this->createReferentTag('92', true, false),
            ],
            'Melun',
            [],
        ];
        yield [
            [
                $this->createReferentTag('77', true, false),
            ],
            'Melun',
            [
                [
                    'name' => 'Melun',
                    'postal_code' => '77000',
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
                    'postal_code' => '94440',
                    'insee_code' => '94048',
                ],
            ],
            [
                '94070',
                [
                    'name' => 'Santeny',
                    'postal_code' => '94440',
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

    private function createReferentTag(string $code, bool $isDepartmentTag, bool $isBoroughTag): ReferentTag
    {
        $tag = $this->createMock(ReferentTag::class);
        $tag->expects($this->any())
            ->method('getCode')
            ->willReturn($code)
        ;
        $tag->expects($this->any())
            ->method('isDepartmentTag')
            ->willReturn($isDepartmentTag)
        ;
        $tag->expects($this->any())
            ->method('isBoroughTag')
            ->willReturn($isBoroughTag)
        ;

        return $tag;
    }
}
