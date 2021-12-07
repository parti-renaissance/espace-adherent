<?php

namespace App\ChezVous\Measure;

use App\Entity\ChezVous\City;
use App\Entity\ChezVous\Measure;
use App\Entity\ChezVous\MeasureType;

class DevoirsFaits extends AbstractMeasure
{
    public const TYPE = 'devoirs_faits';
    public const KEY_PROPORTION_ELEVES = 'proportion_eleves';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function getKeys(): array
    {
        return [
            self::KEY_PROPORTION_ELEVES => true,
        ];
    }

    public static function create(City $city, MeasureType $type, string $proportionEleves): Measure
    {
        $measure = self::createMeasure($city, $type);
        $measure->setPayload(self::createPayload($proportionEleves));

        return $measure;
    }

    public static function createPayload(string $proportionEleves): array
    {
        return [
            self::KEY_PROPORTION_ELEVES => $proportionEleves,
        ];
    }
}
