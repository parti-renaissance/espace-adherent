<?php

namespace Tests\AppBundle\Intl;

use AppBundle\Intl\FranceCitiesBundle;
use PHPUnit\Framework\TestCase;

class FranceCitiesBundleTest extends TestCase
{
    /**
     * @dataProvider providePostalCodes
     */
    public function testGetPostalCodeCities($postalCode, $expectedCities)
    {
        $this->assertEquals($expectedCities, FranceCitiesBundle::getPostalCodeCities($postalCode));
    }

    public function providePostalCodes()
    {
        return [
            [
                '75001',
                [
                    75101 => 'Paris 1er',
                ],
            ],
            [
                '35420',
                [
                    35018 => 'La Bazouge-du-Désert',
                    35111 => 'Le Ferré',
                    35162 => 'Louvigné-du-Désert',
                    35174 => 'Mellé',
                    35190 => 'Monthault',
                    35230 => 'Poilley',
                    35271 => 'Saint-Georges-de-Reintembault',
                    35357 => 'Villamée',
                ],
            ],

            // Réunion
            [
                '97441',
                [
                    97420 => 'Sainte-Suzanne',
                ],
            ],

            // Guadeloupe
            [
                '97122',
                [
                    97103 => 'Baie-Mahault',
                ],
            ],

            // Saint-Pierre-et-Miquelon
            [
                '97500',
                [
                    97501 => 'Miquelon-Langlade',
                    97502 => 'Saint-Pierre',
                ],
            ],
        ];
    }
}
