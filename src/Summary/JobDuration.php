<?php

namespace App\Summary;

class JobDuration
{
    public const PART_TIME = 'temps partiel';
    public const FULL_TIME = 'temps plein';
    public const PUNCTUALLY = 'ponctuellement';

    public const ALL = [
        self::PART_TIME,
        self::FULL_TIME,
        self::PUNCTUALLY,
    ];

    public const CHOICES = [
        'member_summary.job_duration.part_time' => self::PART_TIME,
        'member_summary.job_duration.full_time' => self::FULL_TIME,
        'member_summary.job_duration.punctually' => self::PUNCTUALLY,
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
