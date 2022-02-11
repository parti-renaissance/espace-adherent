<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class AddressInScopeZones extends Constraint
{
    public $message = 'scope.address.not_in_zones';
}
