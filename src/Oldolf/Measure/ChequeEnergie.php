<?php

namespace AppBundle\Oldolf\Measure;

use AppBundle\Entity\Oldolf\City;
use AppBundle\Entity\Oldolf\Measure;

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

    public static function create(City $city, int $nombreBeneficiaires): Measure
    {
        $measure = self::createMeasure($city);
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
