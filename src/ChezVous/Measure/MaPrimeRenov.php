<?php

namespace App\ChezVous\Measure;

use App\Entity\ChezVous\City;
use App\Entity\ChezVous\Measure;
use App\Entity\ChezVous\MeasureType;

class MaPrimeRenov extends AbstractMeasure
{
    public const TYPE = 'ma_prime_renov';
    public const KEY_NOMBRE_FOYERS = 'nombre_foyers';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function getKeys(): array
    {
        return [
            self::KEY_NOMBRE_FOYERS => true,
        ];
    }

    public static function create(City $city, MeasureType $type, int $nombreFoyers): Measure
    {
        $measure = self::createMeasure($city, $type);
        $measure->setPayload(self::createPayload($nombreFoyers));

        return $measure;
    }

    public static function createPayload(int $nombreFoyers): array
    {
        return [
            self::KEY_NOMBRE_FOYERS => $nombreFoyers,
        ];
    }
}
