<?php

namespace Tests\AppBundle\Test\Geocoder;

use AppBundle\Geocoder\Coordinates;
use AppBundle\Geocoder\Exception\GeocodingException;
use AppBundle\Geocoder\GeocoderInterface;

class DummyGeocoder implements GeocoderInterface
{
    private static $coordinates = [
        'paris' => [
            'lat' => 48.8589506,
            'lon' => 2.2773447,
        ],
        'kilchberg, suisse' => [
            'lat' => 47.3222,
            'lon' => 8.5438,
        ],
        '8802 kilchberg, ch' => [
            'lat' => 47.3222,
            'lon' => 8.5438,
        ],
        '6 rue neyret, 69001 lyon 1er, fr' => [
            'lat' => 45.7713288,
            'lon' => 4.8288758,
        ],
        '50 rue de la villette, 69003 lyon 3e, fr' => [
            'lat' => 45.7596356,
            'lon' => 4.8614359,
        ],
        '92 bld victor hugo, 92110 clichy, fr' => [
            'lat' => 48.901058,
            'lon' => 2.318325,
        ],
        '92 boulevard victor hugo, 92110 clichy, fr' => [
            'lat' => 48.901058,
            'lon' => 2.318325,
        ],
        '9 rue du lycée, 06000 nice, fr' => [
            'lat' => 43.699488,
            'lon' => 7.274206,
        ],
        '36 zeppelinstrasse, 8057 zürich, ch' => [
            'lat' => 47.3952329,
            'lon' => 8.5381863,
        ],
        '20 rue francois vitry, 97437 saint benoit, re' => [
            'lat' => -21.041622,
            'lon' => 55.7190445,
        ],
        '18 rue roby petreluzzi, 97110 pointe-à-pitre, gp' => [
            'lat' => 16.245013,
            'lon' => -61.5345199,
        ],
        '45 avenue du maréchal foch, 98714 papeete, pf' => [
            'lat' => -17.5416521,
            'lon' => -149.5684174,
        ],
        'pilgerweg 58, 8802 kilchberg, ch' => [
            'lat' => 47.316558,
            'lon' => 8.55319899999995,
        ],
        'paris 8e, france' => [
            'lat' => 48.866667,
            'lon' => 2.333333,
        ],
        'paris 9e, france' => [
            'lat' => 48.8790183,
            'lon' => 2.3379062,
        ],
        '73100 grésy-sur-aix, fr' => [
            'lat' => 45.7167,
            'lon' => 5.95,
        ],
        '12 pilgerweg, 8802 kilchberg, ch' => [
            'lat' => 47.321569,
            'lon' => 8.549968799999988,
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
