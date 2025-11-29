<?php

declare(strict_types=1);

namespace App\Address;

use App\Geocoder\GeocodableInterface;
use App\Validator\Address as AssertValidAddress;
use App\Validator\GeocodableAddress as AssertGeocodableAddress;

#[AssertGeocodableAddress]
#[AssertValidAddress]
class NullableAddress implements AddressInterface, GeocodableInterface
{
    use AddressTrait;
}
