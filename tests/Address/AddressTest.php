<?php

declare(strict_types=1);

namespace Tests\App\Address;

use App\Address\Address;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

#[Group('address')]
class AddressTest extends AbstractKernelTestCase
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
        $this->assertSame('36 Zeppelinstrasse, 8057 Zürich, Suisse', $address->getGeocodableAddress());
        $this->assertFalse($address->isFrenchAddress());
    }

    public function testCreateValidFrenchAddress()
    {
        $address = new Address();
        $address->setCountry('FR');
        $address->setAddress('6 rue Neyret');
        $address->setCity('69001-69381');

        [, $inseeCode] = explode('-', $address->getCity());
        $city = $this->getFranceCities()->getCityByInseeCode($inseeCode);
        $address->setCityName($city ? $city->getName() : null);

        $this->assertSame('FR', $address->getCountry());
        $this->assertSame('6 rue Neyret', $address->getAddress());
        $this->assertSame('69001-69381', $address->getCity());
        $this->assertSame('69001', $address->getPostalCode());
        $this->assertSame('Lyon 1er', $address->getCityName());
        $this->assertSame('6 rue Neyret, 69001 Lyon 1er, France', $address->getGeocodableAddress());
        $this->assertTrue($address->isFrenchAddress());
    }

    public function testCreateInvalidValidFrenchAddress()
    {
        $this->expectException(\InvalidArgumentException::class);

        $address = new Address();
        $address->setCity('69001');
    }
}
