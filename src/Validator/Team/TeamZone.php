<?php

namespace App\Validator\Team;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class TeamZone extends Constraint
{
    public string $nationalScopeWithZoneMessage = 'team.zone.national_scope_with_zone';
    public string $localScopeWithoutZoneMessage = 'team.zone.local_scope_without_zone';
    public string $localScopeWithUnmanagedZoneMessage = 'team.zone.local_scope_with_unmanaged_zone';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
