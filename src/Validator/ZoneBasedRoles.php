<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ZoneBasedRoles extends Constraint
{
    public string $duplicateRoleTypeMessage = 'adherent.zone_based_role.duplicate_type';
    public string $invalidZoneTypeMessage = 'adherent.zone_based_role.invalid_zone_type';
    public string $emptyZoneMessage = 'adherent.zone_based_role.empty_zone';
    public string $limitZoneMessage = 'adherent.zone_based_role.limit_zone';
    public string $zoneDuplicateMessage = 'adherent.zone_based_role.zone_duplicate';
}
