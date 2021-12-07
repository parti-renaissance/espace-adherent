<?php

namespace App\ChezVous\Measure;

use App\Entity\ChezVous\City;
use App\Entity\ChezVous\Measure;
use App\Entity\ChezVous\MeasureType;

class MaisonDeSante extends AbstractMeasure
{
    public const TYPE = 'maison_de_sante';
    public const KEY_NOMBRE_MAISONS = 'nombre_maisons';
    public const KEY_POURCENTAGE_PROGRESSION = 'pourcentage_progression';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function getKeys(): array
    {
        return [
            self::KEY_NOMBRE_MAISONS => true,
            self::KEY_POURCENTAGE_PROGRESSION => true,
        ];
    }

    public static function create(
        City $city,
        MeasureType $type,
        int $nombreMaisons,
        int $pourcentageProgression
    ): Measure {
        $measure = self::createMeasure($city, $type);
        $measure->setPayload(self::createPayload($nombreMaisons, $pourcentageProgression));

        return $measure;
    }

    public static function createPayload(int $nombreMaisons, int $pourcentageProgression): array
    {
        return [
            self::KEY_NOMBRE_MAISONS => $nombreMaisons,
            self::KEY_POURCENTAGE_PROGRESSION => $pourcentageProgression,
        ];
    }
}
