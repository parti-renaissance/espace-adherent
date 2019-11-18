<?php

namespace AppBundle\Geocoder;

use AppBundle\Geocoder\Exception\GeocodingException;

interface GeocoderInterface
{
    /**
     * Geocodes an address.
     *
     * @throws GeocodingException
     */
    public function geocode(string $address): Coordinates;
}
