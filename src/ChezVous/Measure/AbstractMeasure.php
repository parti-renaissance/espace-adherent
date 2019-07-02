<?php

namespace AppBundle\ChezVous\Measure;

use AppBundle\Entity\ChezVous\City;
use AppBundle\Entity\ChezVous\Measure;

abstract class AbstractMeasure
{
    abstract public static function getType(): string;

    public static function getKeys(): array
    {
        return [];
    }

    public static function createMeasure(City $city): Measure
    {
        return new Measure($city, static::getType());
    }
}
