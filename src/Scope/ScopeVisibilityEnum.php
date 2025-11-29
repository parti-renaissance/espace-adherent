<?php

declare(strict_types=1);

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
