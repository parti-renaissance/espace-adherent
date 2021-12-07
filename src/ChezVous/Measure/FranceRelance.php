<?php

namespace App\ChezVous\Measure;

use App\Entity\ChezVous\City;
use App\Entity\ChezVous\Measure;
use App\Entity\ChezVous\MeasureType;

class FranceRelance extends AbstractMeasure
{
    public const TYPE = 'france_relance';
    public const KEY_NOMBRE_PROJETS = 'nombre_projets';
    public const KEY_EXEMPLE = 'exemple';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function getKeys(): array
    {
        return [
            self::KEY_NOMBRE_PROJETS => true,
            self::KEY_EXEMPLE => true,
        ];
    }

    public static function create(City $city, MeasureType $type, int $nombreProjets, string $exemple): Measure
    {
        $measure = self::createMeasure($city, $type);
        $measure->setPayload(self::createPayload($nombreProjets, $exemple));

        return $measure;
    }

    public static function createPayload(int $nombreProjets, string $exemple): array
    {
        return [
            self::KEY_NOMBRE_PROJETS => $nombreProjets,
            self::KEY_EXEMPLE => $exemple,
        ];
    }
}
