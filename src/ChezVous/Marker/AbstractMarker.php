<?php

namespace AppBundle\ChezVous\Marker;

use AppBundle\Entity\ChezVous\City;
use AppBundle\Entity\ChezVous\Marker;

abstract class AbstractMarker
{
    abstract public static function getType(): string;

    public function createMarker(City $city, string $latitude, string $longitude): Marker
    {
        return new Marker($city, static::getType(), $latitude, $longitude);
    }
}
