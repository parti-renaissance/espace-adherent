<?php

namespace App\Membership;

final class ActivityPositions
{
    const STUDENT = 'student';
    const RETIRED = 'retired';
    const EMPLOYED = 'employed';
    const UNEMPLOYED = 'unemployed';

    const ALL = [
        self::STUDENT,
        self::RETIRED,
        self::EMPLOYED,
        self::UNEMPLOYED,
    ];

    const CHOICES = [
        'adherent.activity_position.student' => self::STUDENT,
        'adherent.activity_position.retired' => self::RETIRED,
        'adherent.activity_position.employed' => self::EMPLOYED,
        'adherent.activity_position.unemployed' => self::UNEMPLOYED,
    ];

    private function __construct()
    {
    }

    public static function all()
    {
        return self::ALL;
    }

    public static function exists(string $position): bool
    {
        return \in_array($position, self::ALL, true);
    }
}
