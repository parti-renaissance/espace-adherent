<?php

namespace App\ChezVous\Measure;

use App\Entity\ChezVous\City;
use App\Entity\ChezVous\Measure;
use App\Entity\ChezVous\MeasureType;

class ChequeEnergie extends AbstractMeasure
{
    public const TYPE = 'cheque_energie';
    public const KEY_NOMBRE_BENEFICIAIRES = 'nombre_beneficiaires';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function getKeys(): array
    {
        return [
            self::KEY_NOMBRE_BENEFICIAIRES => true,
        ];
    }

    public static function create(City $city, MeasureType $type, int $nombreBeneficiaires): Measure
    {
        $measure = self::createMeasure($city, $type);
        $measure->setPayload(self::createPayload($nombreBeneficiaires));

        return $measure;
    }

    public static function createPayload(int $nombreBeneficiaires): array
    {
        return [
            self::KEY_NOMBRE_BENEFICIAIRES => $nombreBeneficiaires,
        ];
    }
}
