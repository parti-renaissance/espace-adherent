<?php

namespace App\Geocoder;

interface GeocodableInterface
{
    /**
     * Returns the geocodable address as a string.
     */
    public function getGeocodableAddress(): string;
}
