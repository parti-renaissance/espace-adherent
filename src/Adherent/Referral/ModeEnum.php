<?php

declare(strict_types=1);

namespace App\Adherent\Referral;

enum ModeEnum: string
{
    case SMS = 'sms';
    case EMAIL = 'email';
}
