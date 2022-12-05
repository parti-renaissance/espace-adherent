<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class ValidAuthorRoleMessageType extends Constraint
{
    public $message = 'Le type de message est invalide';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
