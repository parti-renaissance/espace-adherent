<?php

namespace AppBundle\Oldolf;

use AppBundle\Oldolf\Marker\MaisonServiceAccueilPublic;

class MarkerChoiceLoader
{
    private const TYPES = [
        MaisonServiceAccueilPublic::class,
    ];

    public static function getTypeChoices(): array
    {
        $choices = [];
        foreach (self::TYPES as $typeClass) {
            $type = $typeClass::getType();

            $choices["oldolf.marker.type.$type"] = $type;
        }

        return $choices;
    }
}
