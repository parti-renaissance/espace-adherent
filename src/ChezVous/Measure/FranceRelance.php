<?php

namespace App\ChezVous\Measure;

use App\Entity\ChezVous\City;
use App\Entity\ChezVous\Measure;
use App\Entity\ChezVous\MeasureType;

class FranceRelance extends AbstractMeasure
{
    public const TYPE = 'france_relance';
    public const KEY_NOMBRE_PROJETS = 'nombre_projets';
    public const KEY_EXAMPLE = 'example';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function getKeys(): array
    {
        return [
            self::KEY_NOMBRE_PROJETS => true,
            self::KEY_EXAMPLE => true,
        ];
    }

    public static function create(City $city, MeasureType $type, int $nombreProjets, string $example): Measure
    {
        $measure = self::createMeasure($city, $type);
        $measure->setPayload(self::createPayload($nombreProjets, $example));

        return $measure;
    }

    public static function createPayload(int $nombreProjets, string $example): array
    {
        return [
            self::KEY_NOMBRE_PROJETS => $nombreProjets,
            self::KEY_EXAMPLE => $example,
        ];
    }
}
