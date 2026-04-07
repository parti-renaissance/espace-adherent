<?php

declare(strict_types=1);

namespace App\Firebase;

enum PushTokenUnsubscribeReasonEnum: string
{
    case USER = 'user';
    case TOKEN_UNKNOWN = 'token_unknown';
    case TOKEN_INVALID = 'token_invalid';
}
