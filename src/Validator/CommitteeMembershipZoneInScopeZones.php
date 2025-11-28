<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class CommitteeMembershipZoneInScopeZones extends Constraint
{
    public string $message = 'L\'adhérent ne fait pas partie de votre zone de couverture.';
}
