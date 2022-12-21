<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class ZoneInScopeZones extends Constraint
{
    public string $message = 'local_site.zone.not_in_scope_zones';

    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
