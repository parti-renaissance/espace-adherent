<?php

namespace App\ChezVous;

use App\ChezVous\Marker\DedoublementClasses;
use App\ChezVous\Marker\MaisonServiceAccueilPublic;
use App\ChezVous\Marker\MissionBern;

class MarkerChoiceLoader
{
    private const TYPES = [
        DedoublementClasses::class,
        MaisonServiceAccueilPublic::class,
        MissionBern::class,
    ];

    public static function getTypeChoices(): array
    {
        $choices = [];
        foreach (self::TYPES as $typeClass) {
            $type = $typeClass::getType();

            $choices["chez_vous.marker.type.$type"] = $type;
        }

        return $choices;
    }
}
