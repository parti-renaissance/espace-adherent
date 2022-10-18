<?php

namespace App\Entity\Biography;

class ExecutiveOfficeRoleEnum
{
    public const PRESIDENT = 'president';
    public const EXECUTIVE_OFFICER = 'executive_officer';
    public const EXECUTIVE_OFFICE_MEMBER = 'executive_office_member';
    public const DEPUTY_GENERAL_DELEGATE = 'deputy_general_delegate';
    public const FUNCTIONAL_DELEGATE = 'functional_delegate';
    public const MEMBER_BY_RIGHT = 'member_by_right';

    public const ALL = [
        self::PRESIDENT,
        self::EXECUTIVE_OFFICER,
        self::EXECUTIVE_OFFICE_MEMBER,
        self::DEPUTY_GENERAL_DELEGATE,
        self::FUNCTIONAL_DELEGATE,
        self::MEMBER_BY_RIGHT,
    ];
}
