<?php

namespace AppBundle\ChezVous\Measure;

use AppBundle\Entity\ChezVous\City;
use AppBundle\Entity\ChezVous\Measure;

class MissionBern extends AbstractMeasure
{
    public const TYPE = 'mission_bern';
    public const KEY_LINK = 'lien';
    public const KEY_MONTANT = 'montant';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function getKeys(): array
    {
        return [
            self::KEY_LINK => true,
            self::KEY_MONTANT => false,
        ];
    }

    public static function create(City $city, string $link, ?int $montant): Measure
    {
        $measure = self::createMeasure($city);
        $measure->setPayload(self::createPayload($link, $montant));

        return $measure;
    }

    public static function createPayload(string $link, ?int $montant): array
    {
        return [
            self::KEY_LINK => $link,
            self::KEY_MONTANT => $montant,
        ];
    }
}
