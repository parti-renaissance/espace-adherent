<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class CommitteeMembershipZoneInScopeZones extends Constraint
{
    public string $message = 'L\'adhérent ne fait pas partir de votre zone de couverture.';

    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
