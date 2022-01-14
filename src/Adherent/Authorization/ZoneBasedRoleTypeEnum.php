<?php

namespace App\Adherent\Authorization;

use App\Entity\Geo\Zone;
use App\Scope\ScopeEnum;

final class ZoneBasedRoleTypeEnum
{
    public const ALL = [
        ScopeEnum::CORRESPONDENT,
    ];

    public const ZONE_TYPES = [
        ScopeEnum::CORRESPONDENT => [
            Zone::DEPARTMENT,
        ],
    ];
}
