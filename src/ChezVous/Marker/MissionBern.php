<?php

namespace AppBundle\ChezVous\Marker;

class MissionBern extends AbstractMarker
{
    public const TYPE = 'mission_bern';

    public static function getType(): string
    {
        return self::TYPE;
    }
}
