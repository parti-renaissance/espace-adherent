<?php

namespace App\ChezVous\Measure;

use App\Entity\ChezVous\City;
use App\Entity\ChezVous\Measure;
use App\Entity\ChezVous\MeasureType;

class Apprentissage extends AbstractMeasure
{
    public const TYPE = 'apprentissage';
    public const KEY_NOMBRE_JEUNES = 'nombre_jeunes';
    public const KEY_POURCENTAGE_PROGRESSION = 'pourcentage_progression';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function getKeys(): array
    {
        return [
            self::KEY_NOMBRE_JEUNES => true,
            self::KEY_POURCENTAGE_PROGRESSION => true,
        ];
    }

    public static function create(
        City $city,
        MeasureType $type,
        int $nombreJeunes,
        int $pourcentageProgression
    ): Measure {
        $measure = self::createMeasure($city, $type);
        $measure->setPayload(self::createPayload($nombreJeunes, $pourcentageProgression));

        return $measure;
    }

    public static function createPayload(int $nombreJeunes, int $pourcentageProgression): array
    {
        return [
            self::KEY_NOMBRE_JEUNES => $nombreJeunes,
            self::KEY_POURCENTAGE_PROGRESSION => $pourcentageProgression,
        ];
    }
}
