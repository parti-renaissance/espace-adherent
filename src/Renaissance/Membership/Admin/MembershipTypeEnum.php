<?php

declare(strict_types=1);

namespace App\Renaissance\Membership\Admin;

enum MembershipTypeEnum
{
    public const EXCLUSIVE = 'exclusive';
    public const TERRITOIRES_PROGRES = 'territoires_progres';
    public const AGIR = 'agir';
    public const MODEM = 'modem';
    public const OTHER = 'other';

    public const CHOICES = [
        self::EXCLUSIVE, // rename
        self::TERRITOIRES_PROGRES,
        self::AGIR,
        self::MODEM,
        self::OTHER,
    ];
}
