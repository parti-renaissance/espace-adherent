<?php

namespace App\Summary;

class JobDuration
{
    const PART_TIME = 'temps partiel';
    const FULL_TIME = 'temps plein';
    const PUNCTUALLY = 'ponctuellement';

    const ALL = [
        self::PART_TIME,
        self::FULL_TIME,
        self::PUNCTUALLY,
    ];

    const CHOICES = [
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
