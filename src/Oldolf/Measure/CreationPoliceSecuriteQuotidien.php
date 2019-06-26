<?php

namespace AppBundle\Oldolf\Measure;

class CreationPoliceSecuriteQuotidien extends AbstractMeasure
{
    public const TYPE = 'creation_police_securite_quotidien';

    public static function getType(): string
    {
        return self::TYPE;
    }
}
