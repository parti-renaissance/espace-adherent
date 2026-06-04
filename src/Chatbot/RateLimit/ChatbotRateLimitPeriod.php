<?php

declare(strict_types=1);

namespace App\Chatbot\RateLimit;

enum ChatbotRateLimitPeriod: string
{
    case Minute = 'minute';
    case Hour = 'hour';
    case Day = 'day';

    public function interval(): string
    {
        return match ($this) {
            self::Minute => '1 minute',
            self::Hour => '1 hour',
            self::Day => '1 day',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Minute => 'minute',
            self::Hour => 'heure',
            self::Day => 'jour',
        };
    }
}
