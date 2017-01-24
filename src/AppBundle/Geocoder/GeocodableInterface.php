<?php

namespace AppBundle\Geocoder;

interface GeocodableInterface
{
    /**
     * Returns the geocodable address as a string.
     *
     * @return string
     */
    public function getGeocodableAddress(): string;

    /**
     * Updates the coordinates of the geocodable object thanks to the data
     * stored in the Coordinates value object.
     *
     * @param Coordinates $coordinates
     */
    public function updateCoordinates(Coordinates $coordinates);
}
