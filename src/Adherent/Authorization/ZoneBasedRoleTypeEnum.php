<?php

namespace App\Adherent\Authorization;

use App\Address\AddressInterface;
use App\Entity\Geo\GeoInterface;
use App\Entity\Geo\Zone;
use App\Scope\ScopeEnum;

final class ZoneBasedRoleTypeEnum
{
    public const ALL = [
        ScopeEnum::CORRESPONDENT,
        ScopeEnum::LEGISLATIVE_CANDIDATE,
        ScopeEnum::DEPUTY,
        ScopeEnum::REGIONAL_COORDINATOR,
        ScopeEnum::REGIONAL_DELEGATE,
        ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
        ScopeEnum::PROCURATIONS_MANAGER,
        ScopeEnum::FDE_COORDINATOR,
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
        ScopeEnum::REGIONAL_DELEGATE => [
            Zone::REGION,
        ],
        ScopeEnum::PROCURATIONS_MANAGER => [
            Zone::DEPARTMENT,
            Zone::BOROUGH,
            Zone::FOREIGN_DISTRICT,
            Zone::CITY => [GeoInterface::CITY_PARIS_CODE, GeoInterface::CITY_LYON_CODE, GeoInterface::CITY_MARSEILLE_CODE],
            Zone::COUNTRY => [AddressInterface::FRANCE],
        ],
        ScopeEnum::FDE_COORDINATOR => [
            Zone::FOREIGN_DISTRICT,
        ],
    ];
}
