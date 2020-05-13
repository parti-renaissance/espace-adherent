<?php

namespace App\Geocoder;

interface GeocodableEntityEventInterface
{
    /**
     * Returns the geocodable entity.
     */
    public function getGeocodableEntity(): GeocodableInterface;
}
