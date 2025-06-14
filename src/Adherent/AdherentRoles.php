<?php

namespace App\Adherent;

use App\Adherent\Authorization\ZoneBasedRoleTypeEnum;
use App\Scope\ScopeEnum;

class AdherentRoles
{
    public const GROUP_LOCAL = 'role.group.local';
    public const GROUP_ELECTIONS = 'role.group.elections';
    public const GROUP_NATIONAL = 'role.group.national';
    public const GROUP_OTHER = 'role.group.other';

    public const ALL = [
        self::GROUP_LOCAL => [
            ScopeEnum::REGIONAL_DELEGATE,
            ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
            AdherentRoleEnum::DELEGATED_PRESIDENT_DEPARTMENTAL_ASSEMBLY,
            ScopeEnum::DEPUTY,
            AdherentRoleEnum::DELEGATED_DEPUTY,
            ScopeEnum::SENATOR,
            AdherentRoleEnum::ANIMATOR,
            AdherentRoleEnum::DELEGATED_ANIMATOR,
            AdherentRoleEnum::AGORA_PRESIDENT,
            AdherentRoleEnum::AGORA_GENERAL_SECRETARY,
        ],
        self::GROUP_ELECTIONS => [
            ScopeEnum::CORRESPONDENT,
            ScopeEnum::PROCURATIONS_MANAGER,
            ScopeEnum::LEGISLATIVE_CANDIDATE,
        ],
        self::GROUP_NATIONAL => [
            ScopeEnum::NATIONAL,
            ScopeEnum::NATIONAL_COMMUNICATION,
            ScopeEnum::PAP_NATIONAL_MANAGER,
            ScopeEnum::PHONING_NATIONAL_MANAGER,
        ],
        self::GROUP_OTHER => [
            ScopeEnum::REGIONAL_COORDINATOR,
            ScopeEnum::FDE_COORDINATOR,
            AdherentRoleEnum::PAP_USER,
        ],
    ];

    public static function getZoneBasedRoles(): array
    {
        $zoneBasedRoles = [];

        foreach (self::ALL as $group => $roles) {
            foreach ($roles as $role) {
                if (\in_array($role, ZoneBasedRoleTypeEnum::ALL, true)) {
                    $zoneBasedRoles[$group][] = $role;
                }
            }
        }

        return $zoneBasedRoles;
    }
}
