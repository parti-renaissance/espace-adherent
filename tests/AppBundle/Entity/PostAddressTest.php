<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\PostAddress;

class PostAddressTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateFullFrenchAddress()
    {
        $address = PostAddress::createFrenchAddress('92 bld Victor Hugo', '92110-92024', 48.123456, 5.987654);

        $this->assertSame('FR', $address->getCountry());
        $this->assertSame('92 bld Victor Hugo', $address->getAddress());
        $this->assertSame('92110-92024', $address->getCity());
        $this->assertSame('92110', $address->getPostalCode());
        $this->assertSame('92024', $address->getInseeCode());
        $this->assertSame('Clichy', $address->getCityName());
        $this->assertSame(48.123456, $address->getLatitude());
        $this->assertSame(5.987654, $address->getLongitude());
        $this->assertSame('92 bld Victor Hugo, 92110 Clichy, France', $address->getGeocodableAddress());
    }
}
