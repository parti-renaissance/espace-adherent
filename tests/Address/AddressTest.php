<?php

namespace Tests\App\Address;

use App\Address\Address;
use PHPUnit\Framework\TestCase;

/**
 * @group address
 */
class AddressTest extends TestCase
{
    public function testCreateValidForeignAddress()
    {
        $address = new Address();
        $address->setCountry('CH');
        $address->setAddress('36 Zeppelinstrasse');
        $address->setPostalCode('8057');
        $address->setCityName('Zürich');

        $this->assertSame('CH', $address->getCountry());
        $this->assertSame('36 Zeppelinstrasse', $address->getAddress());
        $this->assertNull($address->getCity());
        $this->assertSame('8057', $address->getPostalCode());
        $this->assertSame('Zürich', $address->getCityName());
        $this->assertSame('36 Zeppelinstrasse, 8057 Zürich, CH', $address->getGeocodableAddress());
        $this->assertFalse($address->isFrenchAddress());
    }

    public function testCreateValidFrenchAddress()
    {
        $address = new Address();
        $address->setCountry('FR');
        $address->setAddress('6 rue Neyret');
        $address->setCity('69001-69381');

        $this->assertSame('FR', $address->getCountry());
        $this->assertSame('6 rue Neyret', $address->getAddress());
        $this->assertSame('69001-69381', $address->getCity());
        $this->assertSame('69001', $address->getPostalCode());
        $this->assertSame('Lyon 1er', $address->getCityName());
        $this->assertSame('6 rue Neyret, 69001 Lyon 1er, FR', $address->getGeocodableAddress());
        $this->assertTrue($address->isFrenchAddress());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateInvalidValidFrenchAddress()
    {
        $address = new Address();
        $address->setCity('69001');
    }
}
