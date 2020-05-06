<?php

namespace App\ChezVous\Measure;

use App\Entity\ChezVous\City;
use App\Entity\ChezVous\Measure;
use App\Entity\ChezVous\MeasureType;

class CreationEntreprise extends AbstractMeasure
{
    public const TYPE = 'creation_entreprises';
    public const KEY_ENTREPRISES = 'entreprises';
    public const KEY_MICRO_ENTREPRISES = 'micro_entreprises';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function getKeys(): array
    {
        return [
            self::KEY_ENTREPRISES => true,
            self::KEY_MICRO_ENTREPRISES => false,
        ];
    }

    public static function create(City $city, MeasureType $type, int $entreprises, ?int $microEntreprises): Measure
    {
        $measure = self::createMeasure($city, $type);
        $measure->setPayload(self::createPayload($entreprises, $microEntreprises));

        return $measure;
    }

    public static function createPayload(int $entreprises, ?int $microEntreprises): array
    {
        return [
            self::KEY_ENTREPRISES => $entreprises,
            self::KEY_MICRO_ENTREPRISES => $microEntreprises,
        ];
    }
}
