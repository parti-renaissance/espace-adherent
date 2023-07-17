<?php

namespace App\Address;

use App\Geocoder\GeocodableInterface;
use App\Validator\Address as AssertValidAddress;
use App\Validator\GeocodableAddress as AssertGeocodableAddress;

/**
 * @AssertValidAddress
 * @AssertGeocodableAddress
 */
class NullableAddress implements AddressInterface, GeocodableInterface
{
    use AddressTrait;
}
