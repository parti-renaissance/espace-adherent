<?php

declare(strict_types=1);

namespace Tests\App\Geocoder;

use App\Geocoder\Coordinates;
use App\Geocoder\Exception\GeocodingException;
use App\Geocoder\Geocoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\App\Test\Geocoder\DummyGeocoder;

class DummyGeocoderTest extends TestCase
{
    public function testGeocodeAddressFails()
    {
        $this->expectException(GeocodingException::class);
        $geocoder = new Geocoder(new DummyGeocoder());
        $geocoder->geocode('12 chemin de Bamby, 69003 Lyon, france');
    }

    #[DataProvider('provideAddress')]
    public function testGeocodeAddress(string $address, float $latitude, float $longitude)
    {
        $geocoder = new Geocoder(new DummyGeocoder());
        $coordinates = $geocoder->geocode($address);

        $this->assertInstanceOf(Coordinates::class, $coordinates);
        $this->assertSame($latitude, $coordinates->getLatitude());
        $this->assertSame($longitude, $coordinates->getLongitude());
    }

    public static function provideAddress(): array
    {
        return [
            [
                '6 rue neyret, 69001 lyon 1er, france',
                45.7713288,
                4.8288758,
            ],
            [
                '6 rue Neyret, 69001 Lyon 1er, france',
                45.7713288,
                4.8288758,
            ],
            [
                '92 boulevard victor hugo, 92110 clichy, france',
                48.901058,
                2.318325,
            ],
            [
                '92 Bld Victor Hugo, 92110 Clichy, france',
                48.901058,
                2.318325,
            ],
        ];
    }
}
