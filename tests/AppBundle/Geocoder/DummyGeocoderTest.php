<?php

namespace Tests\AppBundle\Geocoder;

use AppBundle\Geocoder\Coordinates;
use AppBundle\Geocoder\DummyGeocoder;

class DummyGeocoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \AppBundle\Geocoder\Exception\GeocodingException
     */
    public function testGeocodeAddressFails()
    {
        $geocoder = new DummyGeocoder();
        $geocoder->geocode('12 chemin de Bamby, 69003 Lyon, France');
    }

    /**
     * @dataProvider provideAddress
     */
    public function testGeocodeAddress(string $address, float $latitude, float $longitude)
    {
        $geocoder = new DummyGeocoder();
        $coordinates = $geocoder->geocode($address);

        $this->assertInstanceOf(Coordinates::class, $coordinates);
        $this->assertSame($latitude, $coordinates->getLatitude());
        $this->assertSame($longitude, $coordinates->getLongitude());
    }

    public function provideAddress()
    {
        return [
            [
                '6 rue neyret, 69001 lyon 1er, france',
                45.7713288,
                4.8288758,
            ],
            [
                '6 rue Neyret, 69001 Lyon 1er, France',
                45.7713288,
                4.8288758,
            ],
            [
                '92 boulevard victor hugo, 92110 clichy, france',
                48.901058,
                2.318325,
            ],
            [
                '92 Bld Victor Hugo, 92110 Clichy, France',
                48.901058,
                2.318325,
            ],
        ];
    }
}
