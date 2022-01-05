<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ZoneBasedRoles extends Constraint
{
    public string $duplicateRoleTypeMessage = 'adherent.zone_based_role.duplicate_type';
    public string $invalidZoneTypeMessage = 'adherent.zone_based_role.invalid_zone_type';

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
