<?php

namespace AppBundle\ChezVous\Measure;

use AppBundle\Entity\ChezVous\City;
use AppBundle\Entity\ChezVous\Measure;
use AppBundle\Entity\ChezVous\MeasureType;

class BaisseNombreChomeurs extends AbstractMeasure
{
    public const TYPE = 'baisse_nombre_chomeurs';
    public const KEY_BAISSE_VILLE = 'baisse_ville';
    public const KEY_BAISSE_DEPARTEMENT = 'baisse_departement';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function getKeys(): array
    {
        return [
            self::KEY_BAISSE_VILLE => false,
            self::KEY_BAISSE_DEPARTEMENT => false,
        ];
    }

    public static function create(City $city, MeasureType $type, ?int $baisseVille, ?int $baisseDepartement): Measure
    {
        $measure = self::createMeasure($city, $type);
        $measure->setPayload(self::createPayload($baisseVille, $baisseDepartement));

        return $measure;
    }

    public static function createPayload(?int $baisseVille, ?int $baisseDepartement): array
    {
        return [
            self::KEY_BAISSE_VILLE => $baisseVille,
            self::KEY_BAISSE_DEPARTEMENT => $baisseDepartement,
        ];
    }
}
