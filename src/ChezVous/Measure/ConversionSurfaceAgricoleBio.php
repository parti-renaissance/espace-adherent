<?php

namespace AppBundle\ChezVous\Measure;

use AppBundle\Entity\ChezVous\City;
use AppBundle\Entity\ChezVous\Measure;
use AppBundle\Entity\ChezVous\MeasureType;

class ConversionSurfaceAgricoleBio extends AbstractMeasure
{
    public const TYPE = 'conversion_surface_agricole_bio';
    public const KEY_HECTARES_BIO = 'hectares_bio';
    public const KEY_PROGRESSION = 'progression';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function getKeys(): array
    {
        return [
            self::KEY_HECTARES_BIO => true,
            self::KEY_PROGRESSION => true,
        ];
    }

    public static function create(City $city, MeasureType $type, int $hectaresBio, string $progression): Measure
    {
        $measure = self::createMeasure($city, $type);
        $measure->setPayload(self::createPayload($hectaresBio, $progression));

        return $measure;
    }

    public static function createPayload(int $hectaresBio, string $progression): array
    {
        return [
            self::KEY_HECTARES_BIO => $hectaresBio,
            self::KEY_PROGRESSION => $progression,
        ];
    }
}
