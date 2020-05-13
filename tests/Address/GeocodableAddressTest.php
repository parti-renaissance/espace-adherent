<?php

namespace Tests\App\Address;

use App\Address\Address;
use App\Address\GeocodableAddress;
use PHPUnit\Framework\TestCase;

/**
 * @group address
 */
class GeocodableAddressTest extends TestCase
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

        $geocodableAddress = GeocodableAddress::createFromAddress($address);

        $this->assertContains($addressStr, (string) $geocodableAddress);
        $this->assertContains('Arcueil', (string) $geocodableAddress);
        $this->assertContains($postalCode, (string) $geocodableAddress);
        $this->assertContains($country, (string) $geocodableAddress);
        $this->assertSame('23 rue Ernest Renan, 94110 Arcueil, FR', (string) $geocodableAddress);
    }
}
