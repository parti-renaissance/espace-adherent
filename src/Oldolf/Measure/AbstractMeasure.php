<?php

namespace AppBundle\Oldolf\Measure;

use AppBundle\Entity\Oldolf\City;
use AppBundle\Entity\Oldolf\Measure;

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
