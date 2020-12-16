<?php

namespace App\Validator\Jecoute;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NewsTarget extends Constraint
{
    public $undefinedTarget = 'Vous devez choisir entre une notification globale ou sélectionner une zone de segmentation.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
