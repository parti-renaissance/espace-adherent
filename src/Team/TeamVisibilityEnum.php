<?php

namespace App\Team;

class TeamVisibilityEnum
{
    public const LOCAL = 'local';
    public const NATIONAL = 'national';

    public const ALL = [
        self::LOCAL,
        self::NATIONAL,
    ];
}
