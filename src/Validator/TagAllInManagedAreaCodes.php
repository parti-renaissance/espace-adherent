<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class TagAllInManagedAreaCodes extends Constraint
{
    public $message = 'common.managed_area.codes.invalid_message';
}
