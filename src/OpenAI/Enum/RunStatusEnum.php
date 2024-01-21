<?php

namespace App\OpenAI\Enum;

enum RunStatusEnum: string
{
    case QUEUED = 'queued';
    case IN_PROGRESS = 'in_progress';
    case REQUIRES_ACTION = 'requires_action';
    case CANCELLING = 'cancelling';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';
    case COMPLETED = 'completed';
    case EXPIRED = 'expired';
    case ERROR = 'error';

    public const NEED_REFRESH = [
        self::QUEUED,
        self::IN_PROGRESS,
    ];
}
