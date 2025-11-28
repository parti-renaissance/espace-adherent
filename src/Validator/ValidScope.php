<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ValidScope extends Constraint
{
    public $message = 'Le scope n\'est pas autorisé';
}
