<?php

namespace App\Summary;

class JobLocation
{
    const ON_SITE = 'sur site';
    const ON_REMOTE = 'à distance';

    const ALL = [
        self::ON_SITE,
        self::ON_REMOTE,
    ];

    const CHOICES = [
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
