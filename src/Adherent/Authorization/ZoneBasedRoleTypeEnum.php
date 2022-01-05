<?php

namespace App\Adherent\Authorization;

use App\Entity\Geo\Zone;
use App\Scope\ScopeEnum;

final class ZoneBasedRoleTypeEnum
{
    public const ALL = [
        ScopeEnum::JEMENGAGE_ADMIN,
    ];

    public const ZONE_TYPES = [
        ScopeEnum::JEMENGAGE_ADMIN => [
            Zone::DEPARTMENT,
        ],
    ];
}
