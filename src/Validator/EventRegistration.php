<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class EventRegistration extends Constraint
{
    public $errorAlreadyExists = 'event.registration.already_exists';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
