<?php

namespace AppBundle\Oldolf\Measure;

class MaisonServiceAccueilPublic extends AbstractMeasure
{
    public const TYPE = 'maison_service_accueil_public';

    public static function getType(): string
    {
        return self::TYPE;
    }
}
