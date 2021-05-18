<?php

namespace App\Summary;

class Contract
{
    public const INTERNSHIP = 'stage';
    public const PERMANENT = 'CDI';
    public const TEMPORARY = 'CDD';
    public const VOLUNTEERING = 'bénévolat';
    public const ASSIGNMENT = 'mission';
    public const OTHER = 'autre';

    public const ALL = [
        self::INTERNSHIP,
        self::PERMANENT,
        self::TEMPORARY,
        self::VOLUNTEERING,
        self::ASSIGNMENT,
        self::OTHER,
    ];

    public const CHOICES = [
        'member_summary.contract.internship' => self::INTERNSHIP,
        'member_summary.contract.permanent' => self::PERMANENT,
        'member_summary.contract.temporary' => self::TEMPORARY,
        'member_summary.contract.volunteering' => self::VOLUNTEERING,
        'member_summary.contract.assignment' => self::ASSIGNMENT,
        'member_summary.contract.other' => self::OTHER,
    ];

    private function __construct()
    {
    }

    public static function all()
    {
        return self::ALL;
    }

    public static function getLabel($contract): string
    {
        return \in_array($contract, self::CHOICES, true) && self::OTHER !== $contract ? ucfirst($contract) : '';
    }
}
