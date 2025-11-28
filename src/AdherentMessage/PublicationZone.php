<?php

declare(strict_types=1);

namespace App\AdherentMessage;

use App\Entity\Geo\Zone;
use App\Scope\ScopeEnum;

abstract class PublicationZone
{
    public const ZONE_TYPES = [
        Zone::BOROUGH,
        Zone::CANTON,
        Zone::CITY,
        Zone::DEPARTMENT,
        Zone::REGION,
        Zone::COUNTRY,
        Zone::DISTRICT,
        Zone::FOREIGN_DISTRICT,
        Zone::CUSTOM,
    ];

    public static function availableZoneTypes(string $scope): array
    {
        return match ($scope) {
            ScopeEnum::DEPUTY,
            ScopeEnum::LEGISLATIVE_CANDIDATE,
            ScopeEnum::SENATOR => [
                Zone::BOROUGH,
                Zone::CANTON,
                Zone::CITY,
            ],
            ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY => [
                Zone::BOROUGH,
                Zone::CANTON,
                Zone::CITY,
                Zone::DISTRICT,
            ],
            ScopeEnum::MUNICIPAL_CANDIDATE => [
                Zone::BOROUGH,
                Zone::CITY,
            ],
            ScopeEnum::MUNICIPAL_PILOT => [
                Zone::CITY,
            ],
            ScopeEnum::REGIONAL_COORDINATOR,
            ScopeEnum::REGIONAL_DELEGATE,
            ScopeEnum::CORRESPONDENT => [
                Zone::BOROUGH,
                Zone::CANTON,
                Zone::CITY,
                Zone::DISTRICT,
                Zone::DEPARTMENT,
            ],
            ScopeEnum::FDE_COORDINATOR => [
                Zone::COUNTRY,
                Zone::FOREIGN_DISTRICT,
                Zone::CUSTOM,
            ],
            default => [],
        };
    }
}
