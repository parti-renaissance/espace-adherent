<?php

declare(strict_types=1);

namespace App\Jecoute;

use MyCLabs\Enum\Enum;

class ProfessionEnum extends Enum
{
    public const FARMERS = 'farmers';
    public const CRAFTSMEN = 'craftsmen';
    public const MANAGERIAL_STAFF = 'managerial_staff';
    public const INTERMEDIATE_PROFESSIONS = 'intermediate_professions';
    public const EMPLOYEES = 'employees';
    public const WORKERS = 'workers';
    public const RETIREES = 'retirees';
    public const SELF_CONTRACTOR = 'self_contractor';
    public const STUDENT = 'student';
    public const HOME_PARENT = 'home_parent';
    public const JOBSEEKER = 'jobseeker';
    public const UNEMPLOYED = 'unemployed';

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
            self::UNEMPLOYED,
        ];
    }

    public static function choices(): array
    {
        return [
            self::EMPLOYEES => 'Employé',
            self::WORKERS => 'Ouvrier',
            self::MANAGERIAL_STAFF => 'Cadre',
            self::INTERMEDIATE_PROFESSIONS => 'Profession intermédiaire',
            self::SELF_CONTRACTOR => 'Indépendant et professions libérales',
            self::RETIREES => 'Retraité',
            self::STUDENT => 'Étudiant',
            self::UNEMPLOYED => 'En recherche d\'emploi',
        ];
    }
}
