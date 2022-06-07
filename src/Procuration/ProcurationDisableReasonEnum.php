<?php

namespace App\Procuration;

final class ProcurationDisableReasonEnum
{
    public const BANNED_EMAIL = 'banned_email';
    public const INVALID_EMAIL = 'invalid_email';
    public const BY_PROCURATION_MANAGER = 'by_procuration_manager';

    public const AUTO_DISABLED_REASONS = [
        self::BANNED_EMAIL,
        self::INVALID_EMAIL,
    ];
}
