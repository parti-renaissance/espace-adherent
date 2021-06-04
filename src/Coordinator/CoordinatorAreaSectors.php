<?php

namespace App\Coordinator;

final class CoordinatorAreaSectors
{
    public const COMMITTEE_SECTOR = 'committee_sector';

    private const LABEL_COMMITTEE_SECTOR = 'coordinator.sector.committee';

    private static $all = [
        self::LABEL_COMMITTEE_SECTOR => self::COMMITTEE_SECTOR,
    ];

    public static function getAll(): array
    {
        return static::$all;
    }
}
