<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ValidAuthorRoleMessageType extends Constraint
{
    public $message = 'Le type de message est invalide';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
