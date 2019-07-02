<?php

namespace AppBundle\ChezVous\Measure;

use AppBundle\Entity\ChezVous\City;
use AppBundle\Entity\ChezVous\Measure;

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
            self::KEY_BAISSE_VILLE => true,
            self::KEY_BAISSE_DEPARTEMENT => true,
        ];
    }

    public static function create(City $city, int $baisseVille, int $baisseDepartement): Measure
    {
        $measure = self::createMeasure($city);
        $measure->setPayload(self::createPayload($baisseVille, $baisseDepartement));

        return $measure;
    }

    public static function createPayload(int $baisseVille, int $baisseDepartement): array
    {
        return [
            self::KEY_BAISSE_VILLE => $baisseVille,
            self::KEY_BAISSE_DEPARTEMENT => $baisseDepartement,
        ];
    }
}
