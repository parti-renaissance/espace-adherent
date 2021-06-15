<?php

namespace App\Validator\Coalition;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class EnabledCoalitionUser extends Constraint
{
    public $message = 'coalition.user.invalid';
}
