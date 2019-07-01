<?php

namespace AppBundle\Oldolf\Measure;

class PassCulture extends AbstractMeasure
{
    public const TYPE = 'pass_culture';

    public static function getType(): string
    {
        return self::TYPE;
    }
}
