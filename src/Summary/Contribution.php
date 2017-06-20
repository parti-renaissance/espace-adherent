<?php

namespace AppBundle\Summary;

class Contribution
{
    const VOLUNTEER = 'bénévole';
    const CONTRACTOR = 'prestataire';
    const EMPLOYEE = 'salarié';

    const ALL = [
        self::VOLUNTEER,
        self::CONTRACTOR,
        self::EMPLOYEE,
    ];

    const CHOICES = [
        'member_summary.contribution.volunteer' => self::VOLUNTEER,
        'member_summary.contribution.contractor' => self::CONTRACTOR,
        'member_summary.contribution.employee' => self::EMPLOYEE,
    ];

    private function __construct()
    {
    }

    public static function all()
    {
        return self::ALL;
    }
}
