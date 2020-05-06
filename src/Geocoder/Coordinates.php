<?php

namespace App\Geocoder;

final class Coordinates
{
    private $latitude;
    private $longitude;

    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public static function createFromArray(array $coordinates): self
    {
        if (!isset($coordinates['lat']) || !isset($coordinates['lon'])) {
            throw new \InvalidArgumentException('Missing "lat" or "lon" or both coordinates.');
        }

        return new self($coordinates['lat'], $coordinates['lon']);
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }
}
