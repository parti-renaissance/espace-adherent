<?php

namespace AppBundle\Geocoder;

use AppBundle\Geocoder\Exception\GeocodingException;

/**
 * This class is only meant for unit and functional tests purposes.
 *
 * @internal
 */
class DummyGeocoder implements GeocoderInterface
{
    private static $coordinates = [
        '6 rue neyret, 69001 lyon 1er, france' => [
            'lat' => 45.7713288,
            'lon' => 4.8288758,
        ],
        '50 rue de la villette, 69003 lyon 3e, france' => [
            'lat' => 45.7596356,
            'lon' => 4.8614359,
        ],
        '92 bld victor hugo, 92110 clichy, france' => [
            'lat' => 48.901058,
            'lon' => 2.318325,
        ],
        '92 boulevard victor hugo, 92110 clichy, france' => [
            'lat' => 48.901058,
            'lon' => 2.318325,
        ],
        '9 rue du lycée, 06000 nice, france' => [
            'lat' => 43.699488,
            'lon' => 7.274206,
        ],
        '36 zeppelinstrasse, 8057 zürich, switzerland' => [
            'lat' => 47.3952329,
            'lon' => 8.5381863,
        ],
    ];

    public function geocode(string $address): Coordinates
    {
        $address = mb_strtolower($address);
        if (empty(static::$coordinates[$address])) {
            throw GeocodingException::create($address);
        }

        return Coordinates::createFromArray(static::$coordinates[$address]);
    }
}
