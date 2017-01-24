<?php

namespace AppBundle\Geocoder;

interface GeoPointInterface extends GeocodableInterface
{
    /**
     * Updates the coordinates of the geocodable object thanks to the data
     * stored in the {@link Coordinates} value object.
     *
     * @param Coordinates $coordinates
     */
    public function updateCoordinates(Coordinates $coordinates);

    /**
     * Returns the longitude.
     *
     * @return float
     */
    public function getLongitude();

    /**
     * Returns the latitude.
     *
     * @return float
     */
    public function getLatitude();
}
