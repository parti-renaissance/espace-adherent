<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class FrenchZipCode extends Constraint
{
    public $message = 'common.postal_code.invalid';
}
