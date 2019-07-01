<?php

namespace AppBundle\Oldolf\Marker;

class MaisonServiceAccueilPublic extends AbstractMarker
{
    public const TYPE = 'maison_service_accueil_public';

    public static function getType(): string
    {
        return self::TYPE;
    }
}
