<?php

namespace App\ChezVous\Measure;

use App\Entity\ChezVous\City;
use App\Entity\ChezVous\Measure;
use App\Entity\ChezVous\MeasureType;

abstract class AbstractMeasure
{
    abstract public static function getType(): string;

    public static function getKeys(): array
    {
        return [];
    }

    public static function createMeasure(City $city, MeasureType $type): Measure
    {
        return new Measure($city, $type);
    }
}
