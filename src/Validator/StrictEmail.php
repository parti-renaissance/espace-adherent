<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class StrictEmail extends Constraint
{
    public $message = 'E-email adresse "{{ email }}" est invalide';
}
