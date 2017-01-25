<?php

namespace AppBundle\Geocoder;

use AppBundle\Geocoder\Exception\GeocodingException;

/**
 * This class is only meant for unit and functiontal tests purposes.
 *
 * @internal
 */
class DummyGeocoder implements GeocoderInterface
{
    private static $coordinates = [
        '6 rue neyret, 69001 lyon 1er arrondissement, france' => [
            'lat' => 45.7713288,
            'lon' => 4.8288758,
        ],
        '50 rue de la villette, 69003 lyon 3e arrondissement, france' => [
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
