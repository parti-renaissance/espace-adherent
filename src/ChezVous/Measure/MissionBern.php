<?php

namespace AppBundle\ChezVous\Measure;

use AppBundle\Entity\ChezVous\City;
use AppBundle\Entity\ChezVous\Measure;

class MissionBern extends AbstractMeasure
{
    public const TYPE = 'mission_bern';
    public const KEY_MONTANT = 'montant';
    public const KEY_LINK = 'lien';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function getKeys(): array
    {
        return [
            self::KEY_MONTANT => true,
            self::KEY_LINK => true,
        ];
    }

    public static function create(City $city, int $montant, string $link): Measure
    {
        $measure = self::createMeasure($city);
        $measure->setPayload(self::createPayload($montant, $link));

        return $measure;
    }

    public static function createPayload(int $montant, string $link): array
    {
        return [
            self::KEY_MONTANT => $montant,
            self::KEY_LINK => $link,
        ];
    }
}
