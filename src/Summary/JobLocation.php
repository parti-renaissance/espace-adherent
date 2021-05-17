<?php

namespace App\Summary;

class JobLocation
{
    public const ON_SITE = 'sur site';
    public const ON_REMOTE = 'à distance';

    public const ALL = [
        self::ON_SITE,
        self::ON_REMOTE,
    ];

    public const CHOICES = [
        'member_summary.job_location.on_site' => self::ON_SITE,
        'member_summary.job_location.on_remote' => self::ON_REMOTE,
    ];

    private function __construct()
    {
    }

    public static function all()
    {
        return self::ALL;
    }

    public static function exists(string $duration): bool
    {
        return \in_array($duration, self::ALL, true);
    }
}
