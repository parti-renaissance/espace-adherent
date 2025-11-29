<?php

declare(strict_types=1);

namespace Tests\App\Address;

use App\Address\Address;
use App\Address\GeocodableAddress;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

#[Group('address')]
class GeocodableAddressTest extends AbstractKernelTestCase
{
    public function testCreateGeocodableAddressFromAddress()
    {
        $addressStr = '23 rue Ernest Renan';
        $postalCode = '94110';
        $country = 'FR';

        $address = new Address();
        $address->setCountry($country);
        $address->setAddress($addressStr);
        $address->setPostalCode($postalCode);
        $address->setCity('94110-94003');

        [, $inseeCode] = explode('-', $address->getCity());
        $city = $this->getFranceCities()->getCityByInseeCode($inseeCode);
        $address->setCityName($city ? $city->getName() : null);

        $geocodableAddress = GeocodableAddress::createFromAddress($address);

        $this->assertStringContainsString($addressStr, (string) $geocodableAddress);
        $this->assertStringContainsString('Arcueil', (string) $geocodableAddress);
        $this->assertStringContainsString($postalCode, (string) $geocodableAddress);
        $this->assertStringContainsString('France', (string) $geocodableAddress);
        $this->assertSame('23 rue Ernest Renan, 94110 Arcueil, France', (string) $geocodableAddress);
    }
}
