<?php

namespace AppBundle\Oldolf\Measure;

use AppBundle\Entity\Oldolf\City;
use AppBundle\Entity\Oldolf\Measure;

class SuppressionTaxeHabitation extends AbstractMeasure
{
    public const TYPE = 'suppression_taxe_habitation';
    public const KEY_NOMBRE_FOYERS = 'nombre_foyers';
    public const KEY_BAISSE_2018 = 'baisse_2018';
    public const KEY_BAISSE_2019 = 'baisse_2019';
    public const KEY_BAISSE_TOTAL = 'baisse_total';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function getKeys(): array
    {
        return [
            self::KEY_NOMBRE_FOYERS => true,
            self::KEY_BAISSE_2018 => true,
            self::KEY_BAISSE_2019 => true,
            self::KEY_BAISSE_TOTAL => true,
        ];
    }

    public static function create(
        City $city,
        int $nombreFoyers,
        int $baisse2018,
        int $baisse2019,
        int $baisseTotal
    ): Measure {
        $measure = self::createMeasure($city);
        $measure->setPayload(self::createPayload($nombreFoyers, $baisse2018, $baisse2019, $baisseTotal));

        return $measure;
    }

    public static function createPayload(int $nombreFoyers, int $baisse2018, int $baisse2019, int $baisseTotal): array
    {
        return [
            self::KEY_NOMBRE_FOYERS => $nombreFoyers,
            self::KEY_BAISSE_2018 => $baisse2018,
            self::KEY_BAISSE_2019 => $baisse2019,
            self::KEY_BAISSE_TOTAL => $baisseTotal,
        ];
    }
}
