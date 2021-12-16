<?php

namespace App\ChezVous\Measure;

use App\Entity\ChezVous\City;
use App\Entity\ChezVous\Measure;
use App\Entity\ChezVous\MeasureType;

class DedoublementClasses extends AbstractMeasure
{
    public const TYPE = 'dedoublement_classes';
    public const KEY_TOTAL_ELEVES = 'total_eleves';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function getKeys(): array
    {
        return [
            self::KEY_TOTAL_ELEVES => false,
        ];
    }

    public static function create(City $city, MeasureType $type, ?int $totalEleves): Measure
    {
        $measure = self::createMeasure($city, $type);
        $measure->setPayload(self::createPayload($totalEleves));

        return $measure;
    }

    public static function createPayload(?int $totalEleves): array
    {
        return [
            self::KEY_TOTAL_ELEVES => $totalEleves,
        ];
    }
}
