<?php

declare(strict_types=1);

namespace App\Validator\Scope;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ScopeVisibility extends Constraint
{
    public string $nationalScopeWithZoneMessage = 'scope.zone.national_scope_with_zone';
    public string $localScopeWithoutZoneMessage = 'scope.zone.local_scope_without_zone';
    public string $localScopeWithUnmanagedZoneMessage = 'scope.zone.local_scope_with_unmanaged_zone';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
