<?php

namespace App\ChezVous\Measure;

use App\Entity\ChezVous\City;
use App\Entity\ChezVous\Measure;
use App\Entity\ChezVous\MeasureType;

class EntreprisesAideesCovid extends AbstractMeasure
{
    public const TYPE = 'entreprises_aidees_covid';
    public const KEY_NOMBRE_ENTREPRISES = 'nombre_entreprises';
    public const KEY_POURCENTAGE_SALARIES = 'pourcentage_salaries';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function getKeys(): array
    {
        return [
            self::KEY_NOMBRE_ENTREPRISES => true,
            self::KEY_POURCENTAGE_SALARIES => true,
        ];
    }

    public static function create(
        City $city,
        MeasureType $type,
        int $nombreEntreprises,
        int $pourcentageSalaries
    ): Measure {
        $measure = self::createMeasure($city, $type);
        $measure->setPayload(self::createPayload($nombreEntreprises, $pourcentageSalaries));

        return $measure;
    }

    public static function createPayload(int $nombreEntreprises, int $pourcentageSalaries): array
    {
        return [
            self::KEY_NOMBRE_ENTREPRISES => $nombreEntreprises,
            self::KEY_POURCENTAGE_SALARIES => $pourcentageSalaries,
        ];
    }
}
