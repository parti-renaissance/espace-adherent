<?php

namespace App\Geocoder;

use App\Geocoder\Exception\GeocodingException;

interface GeocoderInterface
{
    /**
     * Geocodes an address.
     *
     * @throws GeocodingException
     */
    public function geocode(string $address): Coordinates;
}
