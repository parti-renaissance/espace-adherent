<?php

namespace Tests\App\Test\Geocoder;

use App\Geocoder\Exception\GeocodingException;
use Geocoder\Collection;
use Geocoder\Geocoder;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class DummyGeocoder implements Geocoder
{
    public static array $coordinates = [
        'paris' => [
            'latitude' => 48.8589506,
            'longitude' => 2.2773447,
        ],
        'kilchberg, suisse' => [
            'latitude' => 47.3222,
            'longitude' => 8.5438,
        ],
        '8802 kilchberg, suisse' => [
            'latitude' => 47.3222,
            'longitude' => 8.5438,
        ],
        '6 rue neyret, 69001 lyon 1er, france' => [
            'latitude' => 45.7713288,
            'longitude' => 4.8288758,
        ],
        '50 rue de la villette, 69003 lyon 3ème, france' => [
            'latitude' => 45.7596356,
            'longitude' => 4.8614359,
        ],
        '92 bld victor hugo, 92110 clichy, france' => [
            'latitude' => 48.901058,
            'longitude' => 2.318325,
        ],
        '92 bd victor hugo, 92110 clichy, france' => [
            'latitude' => 48.901058,
            'longitude' => 2.318325,
        ],
        '92 boulevard victor hugo, 92110 clichy, france' => [
            'latitude' => 48.901058,
            'longitude' => 2.318325,
        ],
        '9 rue du lycée, 06000 nice, france' => [
            'latitude' => 43.699488,
            'longitude' => 7.274206,
        ],
        '36 zeppelinstrasse, 8057 zürich, suisse' => [
            'latitude' => 47.3952329,
            'longitude' => 8.5381863,
        ],
        '20 rue francois vitry, 97437 saint benoit, reunion' => [
            'latitude' => -21.041622,
            'longitude' => 55.7190445,
        ],
        '18 rue roby petreluzzi, 97110 pointe-à-pitre, gp' => [
            'latitude' => 16.245013,
            'longitude' => -61.5345199,
        ],
        '45 avenue du maréchal foch, 98714 papeete, polynésie française' => [
            'latitude' => -17.5416521,
            'longitude' => -149.5684174,
        ],
        'pilgerweg 58, 8802 kilchberg, suisse' => [
            'latitude' => 47.316558,
            'longitude' => 8.55319899999995,
        ],
        'paris 8ème, france' => [
            'latitude' => 48.866667,
            'longitude' => 2.333333,
        ],
        '44 rue des courcelles, 75008 paris, france' => [
            'latitude' => 48.8761,
            'longitude' => 2.3082,
        ],
        '44 rue des courcelles, 75008 paris 8ème, france' => [
            'latitude' => 48.8761,
            'longitude' => 2.3082,
        ],
        'paris 9ème, france' => [
            'latitude' => 48.8790183,
            'longitude' => 2.3379062,
        ],
        '73100 grésy-sur-aix, france' => [
            'latitude' => 45.7167,
            'longitude' => 5.95,
        ],
        '12 pilgerweg, 8802 kilchberg, suisse' => [
            'latitude' => 47.321569,
            'longitude' => 8.549968799999988,
        ],
        'evry' => [
            'latitude' => 48.633333,
            'longitude' => 2.450000,
        ],
        '12 rue des saussaies, 75008 paris 8ème, france' => [
            'latitude' => 48.8713699,
            'longitude' => 2.318119,
        ],
        '62 avenue des champs-élysées, 75008 paris 8ème, france' => [
            'latitude' => 48.870590,
            'longitude' => 2.305370,
        ],
        '92-98 boulevard victor hugo, 92110 clichy, france' => [
            'latitude' => 48.901170,
            'longitude' => 2.316960,
        ],
        '824 avenue du lys, 77190 dammarie-les-lys, france' => [
            'latitude' => 48.5182194,
            'longitude' => 2.624205,
        ],
        '826 avenue du lys, 77190 dammarie-les-lys, france' => [
            'latitude' => 48.5182193,
            'longitude' => 2.624205,
        ],
        '122 rue de mouxy, 73100 mouxy, france' => [
            'latitude' => 45.570898,
            'longitude' => 5.927206,
        ],
        '73100 mouxy, france' => [
            'latitude' => 45.570898,
            'longitude' => 5.927206,
        ],
        '77000 melun, france' => [
            'latitude' => 48.5278939,
            'longitude' => 2.6484923,
        ],
        '2 avenue jean jaurès, 77000 melun, france' => [
            'latitude' => 48.5279449,
            'longitude' => 2.6484386,
        ],
        '3 avenue jean jaurès, 77000 melun, france' => [
            'latitude' => 48.5279282,
            'longitude' => 2.6484148,
        ],
        'melun, france' => [
            'latitude' => 48.5278939,
            'longitude' => 2.6484923,
        ],
        '30 boulevard louis guichoux, 13003 marseille 3ème, france' => [
            'latitude' => 43.325900,
            'longitude' => 5.374680,
        ],
        '226 rue de rivoli, 75001 paris 1er, france' => [
            'latitude' => 48.859599,
            'longitude' => 2.344967,
        ],
        '68 rue du rocher, 75008 paris 8ème, france' => [
            'latitude' => 48.879091,
            'longitude' => 2.316518,
        ],
        'paris 8ème' => [
            'latitude' => 48.866667,
            'longitude' => 2.333333,
        ],
        'paris 9ème' => [
            'latitude' => 48.8790183,
            'longitude' => 2.3379062,
        ],
        '8447+6m base antarctique mario-zucchelli, aq' => [
            'latitude' => 74.6944361,
            'longitude' => 164.1119988,
        ],
        '13 avenue du maréchal juin, 64200 biarritz, france' => [
            'latitude' => 43.4726041,
            'longitude' => -1.5385905,
        ],
    ];

    public function geocode(string $value): Collection
    {
        $value = mb_strtolower($value);
        if (empty(static::$coordinates[$value])) {
            throw GeocodingException::create($value);
        }

        return new AddressCollection([Address::createFromArray(static::$coordinates[$value])]);
    }

    public function reverse(float $latitude, float $longitude): Collection
    {
        return new AddressCollection();
    }

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        return new AddressCollection();
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        return new AddressCollection();
    }

    public function getName(): string
    {
        return 'geocoder';
    }
}
