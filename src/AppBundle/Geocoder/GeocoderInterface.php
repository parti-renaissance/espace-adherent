<?php

namespace AppBundle\Geocoder;

use AppBundle\Geocoder\Exception\GeocodingException;

interface GeocoderInterface
{
    /**
     * Geocodes an address.
     *
     * @param string $address
     *
     * @return Coordinates
     *
     * @throws GeocodingException
     */
    public function geocode(string $address): Coordinates;
}
