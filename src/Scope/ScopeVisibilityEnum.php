<?php

namespace App\Scope;

class ScopeVisibilityEnum
{
    public const LOCAL = 'local';
    public const NATIONAL = 'national';

    public const ALL = [
        self::LOCAL,
        self::NATIONAL,
    ];
}
