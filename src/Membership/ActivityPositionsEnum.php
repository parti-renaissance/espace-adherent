<?php

declare(strict_types=1);

namespace App\Membership;

final class ActivityPositionsEnum
{
    public const STUDENT = 'student';
    public const RETIRED = 'retired';
    public const EMPLOYED = 'employed';
    public const UNEMPLOYED = 'unemployed';
    public const SELF_EMPLOYED_AND_LIBERAL_PROFESSIONS = 'self_employed_and_liberal_professions';
    public const WORKER = 'worker';
    public const INTERMEDIATE_PROFESSION = 'intermediate_profession';
    public const EXECUTIVE = 'executive';

    public const ALL = [
        self::STUDENT,
        self::RETIRED,
        self::EMPLOYED,
        self::UNEMPLOYED,
        self::SELF_EMPLOYED_AND_LIBERAL_PROFESSIONS,
        self::WORKER,
        self::INTERMEDIATE_PROFESSION,
        self::EXECUTIVE,
    ];

    public const CHOICES = [
        'adherent.activity_position.student' => self::STUDENT,
        'adherent.activity_position.retired' => self::RETIRED,
        'adherent.activity_position.employed' => self::EMPLOYED,
        'adherent.activity_position.unemployed' => self::UNEMPLOYED,
        'adherent.activity_position.self_employed_and_liberal_professions' => self::SELF_EMPLOYED_AND_LIBERAL_PROFESSIONS,
        'adherent.activity_position.worker' => self::WORKER,
        'adherent.activity_position.intermediate_profession' => self::INTERMEDIATE_PROFESSION,
        'adherent.activity_position.executive' => self::EXECUTIVE,
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
