<?php

namespace App\Renaissance\Membership\Admin;

enum MembershipTypeEnum
{
    public const EXCLUSIVE = 'exclusive';
    public const TERRITOIRES_PROGRES = 'territoires_progres';
    public const AGIR = 'agir';

    public const CHOICES = [
        self::EXCLUSIVE,
        self::TERRITOIRES_PROGRES,
        self::AGIR,
    ];
}
