<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class ValidScope extends Constraint
{
    public $message = 'Le scope n\'est pas autorisé';
}
