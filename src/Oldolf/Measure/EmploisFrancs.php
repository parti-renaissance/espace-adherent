<?php

namespace AppBundle\Oldolf\Measure;

class EmploisFrancs extends AbstractMeasure
{
    public const TYPE = 'emplois_francs';

    public static function getType(): string
    {
        return self::TYPE;
    }
}
