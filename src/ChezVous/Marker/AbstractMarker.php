<?php

namespace App\ChezVous\Marker;

use App\Entity\ChezVous\City;
use App\Entity\ChezVous\Marker;

abstract class AbstractMarker
{
    abstract public static function getType(): string;

    public function createMarker(City $city, string $latitude, string $longitude): Marker
    {
        return new Marker($city, static::getType(), $latitude, $longitude);
    }
}
