<?php

namespace AppBundle\Oldolf\Measure;

use AppBundle\Entity\Oldolf\City;
use AppBundle\Entity\Oldolf\Measure;

class CreationEntreprise extends AbstractMeasure
{
    public const TYPE = 'creation_entreprises';
    public const KEY_ENTREPRISES = 'entreprises';
    public const KEY_MICRO_ENTREPRISES = 'micro_entreprises';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function getKeys(): array
    {
        return [
            self::KEY_ENTREPRISES => true,
            self::KEY_MICRO_ENTREPRISES => false,
        ];
    }

    public static function create(City $city, int $entreprises, ?int $microEntreprises): Measure
    {
        $measure = self::createMeasure($city);
        $measure->setPayload(self::createPayload($entreprises, $microEntreprises));

        return $measure;
    }

    public static function createPayload(int $entreprises, ?int $microEntreprises): array
    {
        $payload = [
            self::KEY_ENTREPRISES => $entreprises,
        ];

        if (!empty($microEntreprises)) {
            $payload[self::KEY_MICRO_ENTREPRISES] = $microEntreprises;
        }

        return $payload;
    }
}
