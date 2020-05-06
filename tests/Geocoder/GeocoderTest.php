<?php

namespace Tests\App\Geocoder;

use App\Geocoder\Coordinates;
use App\Geocoder\Geocoder;
use Geocoder\Geocoder as BazingaGeocoder;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Model\Coordinates as BazingaCoordinates;
use PHPUnit\Framework\TestCase;

class GeocoderTest extends TestCase
{
    const ADDRESS = '92 bld victor hugo, 92110 clichy, france';

    private $adapter;
    private $geocoder;

    public function testGeocodeAddressSucceeds()
    {
        $addresses = new AddressCollection([
            new Address(new BazingaCoordinates(48.901058, 2.318325)),
            // the second one will be ignored
            new Address(new BazingaCoordinates(48.901053, 2.318321)),
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

    /**
     * @expectedException \App\Geocoder\Exception\GeocodingException
     */
    public function testGeocodeAddressFails()
    {
        $this
            ->adapter
            ->expects($this->once())
            ->method('geocode')
            ->with(self::ADDRESS)
            ->willThrowException(new \Exception('Geocoding failed'))
        ;

        $this->geocoder->geocode(self::ADDRESS);
    }

    /**
     * @expectedException \App\Geocoder\Exception\GeocodingException
     */
    public function testCannotGeocodeAddress()
    {
        $this
            ->adapter
            ->expects($this->once())
            ->method('geocode')
            ->with(self::ADDRESS)
            ->willReturn(new AddressCollection())
        ;

        $this->geocoder->geocode(self::ADDRESS);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->adapter = $this->getMockBuilder(BazingaGeocoder::class)->getMock();
        $this->geocoder = new Geocoder($this->adapter);
    }

    protected function tearDown()
    {
        $this->adapter = null;
        $this->geocoder = null;

        parent::tearDown();
    }
}
