<?php

namespace App\Chatbot\Enum;

use App\OpenAI\Enum\RunStatusEnum as OpenAIRunStatusEnum;

enum RunStatusEnum: string
{
    case QUEUED = 'queued';
    case IN_PROGRESS = 'in_progress';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';
    case COMPLETED = 'completed';
    case UNKNOWN = 'unknown';

    public const NEED_REFRESH = [
        self::QUEUED,
        self::IN_PROGRESS,
    ];

    public static function fromOpenAI(OpenAIRunStatusEnum $runStatus): self
    {
        return match ($runStatus) {
            OpenAIRunStatusEnum::QUEUED => self::QUEUED,
            OpenAIRunStatusEnum::IN_PROGRESS => self::IN_PROGRESS,
            OpenAIRunStatusEnum::COMPLETED => self::COMPLETED,
            OpenAIRunStatusEnum::CANCELLING, OpenAIRunStatusEnum::CANCELLED => self::CANCELLED,
            OpenAIRunStatusEnum::REQUIRES_ACTION, OpenAIRunStatusEnum::EXPIRED, OpenAIRunStatusEnum::FAILED => self::FAILED,
            default => self::UNKNOWN
        };
    }
}
