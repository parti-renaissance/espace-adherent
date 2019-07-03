<?php

namespace AppBundle\ChezVous;

use AppBundle\ChezVous\Marker\DedoublementClasses;
use AppBundle\ChezVous\Marker\MaisonServiceAccueilPublic;

class MarkerChoiceLoader
{
    private const TYPES = [
        DedoublementClasses::class,
        MaisonServiceAccueilPublic::class,
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
