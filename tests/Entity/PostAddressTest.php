<?php

namespace Tests\App\Entity;

use App\Entity\PostAddress;
use PHPUnit\Framework\TestCase;

class PostAddressTest extends TestCase
{
    public function testCreateFullFrenchAddress()
    {
        $address = PostAddress::createFrenchAddress('92 bld Victor Hugo', '92110-92024', null, 48.123456, 5.987654);

        $this->assertSame('FR', $address->getCountry());
        $this->assertSame('92 bld Victor Hugo', $address->getAddress());
        $this->assertSame('92110-92024', $address->getCity());
        $this->assertSame('92110', $address->getPostalCode());
        $this->assertSame('92024', $address->getInseeCode());
        $this->assertSame('Clichy', $address->getCityName());
        $this->assertSame(48.123456, $address->getLatitude());
        $this->assertSame(5.987654, $address->getLongitude());
        $this->assertSame('92 bld Victor Hugo, 92110 Clichy, FR', $address->getGeocodableAddress());
    }

    public function testCreateFullForeignAddress()
    {
        $address = PostAddress::createForeignAddress(
            'US',
            '20500',
            'Washington, DC',
            '1600 Pennsylvania Ave NW',
            null,
            48.123456,
            5.987654
        );

        $this->assertSame('US', $address->getCountry());
        $this->assertSame('1600 Pennsylvania Ave NW', $address->getAddress());
        $this->assertSame('20500', $address->getPostalCode());
        $this->assertNull($address->getCity());
        $this->assertNull($address->getInseeCode());
        $this->assertSame('Washington, DC', $address->getCityName());
        $this->assertSame(48.123456, $address->getLatitude());
        $this->assertSame(5.987654, $address->getLongitude());
        $this->assertSame('1600 Pennsylvania Ave NW, 20500 Washington, DC, US', $address->getGeocodableAddress());
    }

    public function testEquals()
    {
        $address1 = PostAddress::createFrenchAddress('92 bld Victor Hugo', '92110-92024');
        $address2 = PostAddress::createFrenchAddress('92 bld Victor Hugo', '92110-92024');

        $this->assertTrue($address1->equals($address2));
        $this->assertTrue($address2->equals($address1));
    }

    public function testNotEquals()
    {
        $address1 = PostAddress::createFrenchAddress('92 bld Victor Hugo', '92110-92024');
        $address2 = PostAddress::createFrenchAddress('94 bld Victor Hugo', '92110-92024');

        $this->assertFalse($address1->equals($address2));
        $this->assertFalse($address2->equals($address1));
    }
}
