<?php

namespace App\Entity\MyTeam;

use MyCLabs\Enum\Enum;

class DelegatedAccessEnum extends Enum
{
    public const TYPE_REFERENT = 'referent';
    public const TYPE_DEPUTY = 'deputy';
    public const TYPE_SENATOR = 'senator';
    public const TYPE_MUNICIPAL_CHIEF = 'municipal_chief';

    public const TYPES = [
        self::TYPE_REFERENT,
        self::TYPE_DEPUTY,
        self::TYPE_SENATOR,
        self::TYPE_MUNICIPAL_CHIEF,
    ];

    public static function getAccessesForType(string $type): array
    {
        $accesses = DelegatedAccess::ACCESSES;
        if (self::TYPE_DEPUTY === $type) {
            $accesses[] = DelegatedAccess::ACCESS_COMMITTEE;
        }

        if (self::TYPE_REFERENT === $type) {
            $accesses[] = DelegatedAccess::ACCESS_JECOUTE;
            $accesses[] = DelegatedAccess::ACCESS_CITIZEN_PROJECTS;
            $accesses[] = DelegatedAccess::ACCESS_ELECTED_REPRESENTATIVES;
        }

        return $accesses;
    }
}
