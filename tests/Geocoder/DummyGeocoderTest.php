<?php

namespace Tests\App\Geocoder;

use App\Geocoder\Coordinates;
use PHPUnit\Framework\TestCase;
use Tests\App\Test\Geocoder\DummyGeocoder;

class DummyGeocoderTest extends TestCase
{
    /**
     * @expectedException \App\Geocoder\Exception\GeocodingException
     */
    public function testGeocodeAddressFails()
    {
        $geocoder = new DummyGeocoder();
        $geocoder->geocode('12 chemin de Bamby, 69003 Lyon, FR');
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
                '6 rue neyret, 69001 lyon 1er, fr',
                45.7713288,
                4.8288758,
            ],
            [
                '6 rue Neyret, 69001 Lyon 1er, fr',
                45.7713288,
                4.8288758,
            ],
            [
                '92 boulevard victor hugo, 92110 clichy, fr',
                48.901058,
                2.318325,
            ],
            [
                '92 Bld Victor Hugo, 92110 Clichy, FR',
                48.901058,
                2.318325,
            ],
        ];
    }
}
