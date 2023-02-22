<?php

namespace App\Adherent\Authorization;

use App\Entity\Geo\Zone;
use App\Scope\ScopeEnum;

final class ZoneBasedRoleTypeEnum
{
    public const ALL = [
        ScopeEnum::CORRESPONDENT,
        ScopeEnum::LEGISLATIVE_CANDIDATE,
        ScopeEnum::DEPUTY,
        ScopeEnum::REGIONAL_COORDINATOR,
        ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
    ];

    public const ZONE_TYPE_CONDITIONS = [
        ScopeEnum::CORRESPONDENT => [
            Zone::DEPARTMENT,
        ],
        ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY => [
            Zone::DEPARTMENT,
            Zone::CUSTOM => [Zone::FDE_CODE],
        ],
        ScopeEnum::LEGISLATIVE_CANDIDATE => [
            Zone::DISTRICT,
            Zone::FOREIGN_DISTRICT,
        ],
        ScopeEnum::DEPUTY => [
            Zone::DISTRICT,
            Zone::FOREIGN_DISTRICT,
        ],
        ScopeEnum::REGIONAL_COORDINATOR => [
            Zone::REGION,
        ],
    ];
}
