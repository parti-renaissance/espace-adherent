<?php

namespace App\Validator\Scope;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class ScopeVisibility extends Constraint
{
    public string $nationalScopeWithZoneMessage = 'scope.zone.national_scope_with_zone';
    public string $localScopeWithoutZoneMessage = 'scope.zone.local_scope_without_zone';
    public string $localScopeWithUnmanagedZoneMessage = 'scope.zone.local_scope_with_unmanaged_zone';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
