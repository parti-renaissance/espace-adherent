<?php

namespace AppBundle\ChezVous\Measure;

use AppBundle\Entity\ChezVous\City;
use AppBundle\Entity\ChezVous\Measure;

class DedoublementClasses extends AbstractMeasure
{
    public const TYPE = 'dedoublement_classes';
    public const KEY_TOTAL_CP_CE1 = 'total_cp_ce1';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function getKeys(): array
    {
        return [
            self::KEY_TOTAL_CP_CE1 => true,
        ];
    }

    public static function create(City $city, int $totalCpCe1): Measure
    {
        $measure = self::createMeasure($city);
        $measure->setPayload(self::createPayload($totalCpCe1));

        return $measure;
    }

    public static function createPayload(int $totalCpCe1): array
    {
        return [
            self::KEY_TOTAL_CP_CE1 => $totalCpCe1,
        ];
    }
}
