<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class Repeated extends Constraint
{
    public $message = 'common.repeated';
}
