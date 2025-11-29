<?php

declare(strict_types=1);

namespace Tests\App\Geocoder;

use App\Geocoder\Coordinates;
use App\Geocoder\Exception\GeocodingException;
use App\Geocoder\Geocoder;
use Geocoder\Geocoder as BazingaGeocoder;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use PHPUnit\Framework\TestCase;

class GeocoderTest extends TestCase
{
    public const ADDRESS = '92 bld victor hugo, 92110 clichy, france';

    private $adapter;
    private $geocoder;

    public function testGeocodeAddressSucceeds()
    {
        $addresses = new AddressCollection([
            Address::createFromArray(['latitude' => 48.901058, 'longitude' => 2.318325]),
            // the second one will be ignored
            Address::createFromArray(['latitude' => 48.901053, 'longitude' => 2.318321]),
        ]);

        $this
            ->adapter
            ->expects($this->once())
            ->method('geocode')
            ->with(self::ADDRESS)
            ->willReturn($addresses)
        ;

        $coordinates = $this->geocoder->geocode(self::ADDRESS);

        $this->assertInstanceOf(Coordinates::class, $coordinates);
        $this->assertSame(48.901058, $coordinates->getLatitude());
        $this->assertSame(2.318325, $coordinates->getLongitude());
    }

    public function testGeocodeAddressFails()
    {
        $this->expectException(GeocodingException::class);
        $this
            ->adapter
            ->expects($this->once())
            ->method('geocode')
            ->with(self::ADDRESS)
            ->willThrowException(new \Exception('Geocoding failed'))
        ;

        $this->geocoder->geocode(self::ADDRESS);
    }

    public function testCannotGeocodeAddress()
    {
        $this->expectException(GeocodingException::class);
        $this
            ->adapter
            ->expects($this->once())
            ->method('geocode')
            ->with(self::ADDRESS)
            ->willReturn(new AddressCollection())
        ;

        $this->geocoder->geocode(self::ADDRESS);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->adapter = $this->getMockBuilder(BazingaGeocoder::class)->getMock();
        $this->geocoder = new Geocoder($this->adapter);
    }

    protected function tearDown(): void
    {
        $this->adapter = null;
        $this->geocoder = null;

        parent::tearDown();
    }
}
