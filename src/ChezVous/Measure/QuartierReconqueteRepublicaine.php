<?php

namespace AppBundle\ChezVous\Measure;

class QuartierReconqueteRepublicaine extends AbstractMeasure
{
    public const TYPE = 'quartier_reconquete_republicaine';

    public static function getType(): string
    {
        return self::TYPE;
    }
}
