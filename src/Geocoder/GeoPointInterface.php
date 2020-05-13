<?php

namespace App\Geocoder;

interface GeoPointInterface extends GeocodableInterface
{
    /**
     * Updates the coordinates of the geocodable object thanks to the data
     * stored in the {@link Coordinates} value object.
     */
    public function updateCoordinates(Coordinates $coordinates): void;

    /**
     * Returns the longitude.
     *
     * @return float
     */
    public function getLongitude(): ?float;

    /**
     * Returns the latitude.
     *
     * @return float
     */
    public function getLatitude(): ?float;

    public function getGeocodableHash(): ?string;

    public function setGeocodableHash(string $hash): void;
}
