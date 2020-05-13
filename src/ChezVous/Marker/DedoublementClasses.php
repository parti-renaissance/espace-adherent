<?php

namespace App\ChezVous\Marker;

class DedoublementClasses extends AbstractMarker
{
    public const TYPE = 'dedoublement_classes';

    public static function getType(): string
    {
        return self::TYPE;
    }
}
