<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class InvalidEmailAddress extends Constraint
{
    public string $message = 'Oups, quelque chose s\'est mal passé';
}
