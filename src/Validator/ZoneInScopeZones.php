<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ZoneInScopeZones extends Constraint
{
    public string $message = 'department_site.zone.not_in_scope_zones';
}
