<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class NewUserPassword extends Constraint
{
    public $notMatchingMessage = 'Les mots de passe ne correspondent pas.';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
