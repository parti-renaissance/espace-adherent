<?php

namespace AppBundle\Oldolf\Measure;

use AppBundle\Entity\Oldolf\City;
use AppBundle\Entity\Oldolf\Measure;

class PrimeConversionAutomobile extends AbstractMeasure
{
    public const TYPE = 'prime_conversion_automobile';
    public const KEY_NOMBRE_BENEFICIAIRES = 'nombre_beneficiaires';
    public const KEY_MONTANT_MOYEN = 'montant_moyen';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function getKeys(): array
    {
        return [
            self::KEY_NOMBRE_BENEFICIAIRES => true,
            self::KEY_MONTANT_MOYEN => true,
        ];
    }

    public static function create(City $city, int $nombreBeneficiaires, int $montantMoyen): Measure
    {
        $measure = self::createMeasure($city);
        $measure->setPayload(self::createPayload($nombreBeneficiaires, $montantMoyen));

        return $measure;
    }

    public static function createPayload(int $nombreBeneficiaires, int $montantMoyen): array
    {
        return [
            self::KEY_NOMBRE_BENEFICIAIRES => $nombreBeneficiaires,
            self::KEY_MONTANT_MOYEN => $montantMoyen,
        ];
    }
}
