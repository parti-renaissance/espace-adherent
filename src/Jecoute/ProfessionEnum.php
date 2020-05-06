<?php

namespace App\Jecoute;

use MyCLabs\Enum\Enum;

class ProfessionEnum extends Enum
{
    public const FARMERS = 'farmers';
    public const CRAFTSMEN = 'craftsmen';
    public const MANAGERIAL_STAFF = 'managerial staff';
    public const INTERMEDIATE_PROFESSIONS = 'intermediate_professions';
    public const EMPLOYEES = 'employees';
    public const WORKERS = 'workers';
    public const RETIREES = 'retirees';
    public const SELF_CONTRACTOR = 'self_contractor';
    public const STUDENT = 'student';
    public const HOME_PARENT = 'home_parent';
    public const JOBSEEKER = 'jobseeker';

    public static function all(): array
    {
        return [
            self::FARMERS,
            self::CRAFTSMEN,
            self::MANAGERIAL_STAFF,
            self::INTERMEDIATE_PROFESSIONS,
            self::EMPLOYEES,
            self::WORKERS,
            self::RETIREES,
            self::SELF_CONTRACTOR,
            self::STUDENT,
            self::HOME_PARENT,
            self::JOBSEEKER,
        ];
    }
}
