<?php

declare(strict_types=1);

namespace Tests\App\Address;

use App\Address\Address;
use App\Address\PostAddressFactory;
use App\Entity\PostAddress;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

#[Group('address')]
class PostAddressFactoryTest extends AbstractKernelTestCase
{
    public function testCreateFrenchAddress()
    {
        $address = new Address();
        $address->setCountry('FR');
        $address->setAddress('6 rue Neyret');
        $address->setCity('69001-69381');

        [$postalCode, $inseeCode] = explode('-', $address->getCity());
        $city = $this->getFranceCities()->getCityByInseeCode($inseeCode);
        $address->setCityName($city ? $city->getName() : null);

        $factory = $this->getFactory();
        $postAddress = $factory->createFromAddress($address);

        $this->assertInstanceOf(PostAddress::class, $postAddress);
        $this->assertSame('FR', $postAddress->getCountry());
        $this->assertSame('6 rue Neyret', $postAddress->getAddress());
        $this->assertSame('69001-69381', $postAddress->getCity());
        $this->assertSame('69001', $postAddress->getPostalCode());
        $this->assertSame('69381', $postAddress->getInseeCode());
        $this->assertSame('Lyon 1er', $postAddress->getCityName());
        $this->assertSame('6 rue Neyret, 69001 Lyon 1er, France', $postAddress->getGeocodableAddress());
        $this->assertNull($postAddress->getLatitude());
        $this->assertNull($postAddress->getLongitude());
    }

    public function testCreateForeignAddress()
    {
        $address = new Address();
        $address->setCountry('CH');
        $address->setAddress('36 Zeppelinstrasse');
        $address->setPostalCode('8057');
        $address->setCityName('Zürich');

        $factory = $this->getFactory();
        $postAddress = $factory->createFromAddress($address);

        $this->assertInstanceOf(PostAddress::class, $postAddress);
        $this->assertSame('CH', $postAddress->getCountry());
        $this->assertSame('36 Zeppelinstrasse', $postAddress->getAddress());
        $this->assertNull($postAddress->getCity());
        $this->assertSame('8057', $postAddress->getPostalCode());
        $this->assertNull($postAddress->getInseeCode());
        $this->assertSame('Zürich', $postAddress->getCityName());
        $this->assertSame('36 Zeppelinstrasse, 8057 Zürich, Suisse', $postAddress->getGeocodableAddress());
        $this->assertNull($postAddress->getLatitude());
        $this->assertNull($postAddress->getLongitude());
    }

    private function getFactory(): PostAddressFactory
    {
        return new PostAddressFactory();
    }
}
