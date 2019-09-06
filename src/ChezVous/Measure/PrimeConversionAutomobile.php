<?php

namespace AppBundle\ChezVous\Measure;

use AppBundle\Entity\ChezVous\City;
use AppBundle\Entity\ChezVous\Measure;
use AppBundle\Entity\ChezVous\MeasureType;

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

    public static function create(City $city, MeasureType $type, int $nombreBeneficiaires, int $montantMoyen): Measure
    {
        $measure = self::createMeasure($city, $type);
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
