<?php

namespace App\Coordinator;

final class CoordinatorAreaSectors
{
    public const COMMITTEE_SECTOR = 'committee_sector';
    public const CITIZEN_PROJECT_SECTOR = 'citizen_project_sector';

    private const LABEL_COMMITTEE_SECTOR = 'coordinator.sector.committee';
    private const LABEL_CITIZEN_PROJECT_SECTOR = 'coordinator.sector.citizen_project';

    private static $all = [
        self::LABEL_COMMITTEE_SECTOR => self::COMMITTEE_SECTOR,
        self::LABEL_CITIZEN_PROJECT_SECTOR => self::CITIZEN_PROJECT_SECTOR,
    ];

    public static function getAll(): array
    {
        return static::$all;
    }
}
