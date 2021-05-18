<?php

namespace App\Summary;

class Contribution
{
    public const VOLUNTEER = 'bénévole';
    public const CONTRACTOR = 'prestataire';
    public const EMPLOYEE = 'salarié';

    public const ALL = [
        self::VOLUNTEER,
        self::CONTRACTOR,
        self::EMPLOYEE,
    ];

    public const CHOICES = [
        'member_summary.contribution.volunteer' => self::VOLUNTEER,
        'member_summary.contribution.contractor' => self::CONTRACTOR,
        'member_summary.contribution.employee' => self::EMPLOYEE,
    ];

    public const MISSION_LABELS = [
        self::VOLUNTEER => 'Missions de bénévolat',
        self::CONTRACTOR => 'Prestations',
        self::EMPLOYEE => 'Salariat',
    ];

    private function __construct()
    {
    }

    public static function all()
    {
        return self::ALL;
    }
}
