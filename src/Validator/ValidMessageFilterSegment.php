<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class ValidMessageFilterSegment extends Constraint
{
    public $message = 'Le segment n\'est pas autorisé';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
