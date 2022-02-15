<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class EventCategory extends Constraint
{
    public $errorPath = 'category';
    public $message = 'Catégorie est requise.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
