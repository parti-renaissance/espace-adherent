<?php

declare(strict_types=1);

namespace App\Geocoder;

interface GeoPointInterface extends GeocodableInterface
{
    /**
     * Updates the coordinates of the geocodable object thanks to the data
     * stored in the {@link Coordinates} value object.
     */
    public function updateCoordinates(Coordinates $coordinates): void;

    public function resetCoordinates(): void;

    /**
     * Returns the longitude.
     */
    public function getLongitude(): ?float;

    /**
     * Returns the latitude.
     */
    public function getLatitude(): ?float;

    public function getGeocodableHash(): ?string;

    public function setGeocodableHash(string $hash): void;
}
