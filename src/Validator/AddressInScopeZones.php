<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class AddressInScopeZones extends Constraint
{
    public $message = 'scope.address.not_in_zones';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
