<?php

declare(strict_types=1);

namespace App\Chatbot\RateLimit;

final class ChatbotRateLimitConfig
{
    /** @var array<value-of<ChatbotRateLimitPeriod>, int> */
    private const array GLOBAL_LIMITS = [
        'day' => 9000,
    ];

    /** @var array<value-of<ChatbotUserTier>, array<value-of<ChatbotRateLimitPeriod>, int>> */
    private const array TIER_LIMITS = [
        'public' => ['minute' => 0, 'hour' => 0, 'day' => 0],
        'contact' => ['minute' => 3, 'hour' => 5, 'day' => 10],
        'sympathisant' => ['minute' => 3, 'hour' => 5, 'day' => 10],
        'adherent' => ['minute' => 3, 'hour' => 5, 'day' => 20],
        'adherent_a_jour' => ['minute' => 3, 'hour' => 5, 'day' => 20],
        'cadre' => ['minute' => 3, 'hour' => 5, 'day' => 50],
    ];

    public static function getGlobalLimit(ChatbotRateLimitPeriod $period): int
    {
        return self::GLOBAL_LIMITS[$period->value] ?? -1;
    }

    public static function getTierLimit(ChatbotUserTier $tier, ChatbotRateLimitPeriod $period): int
    {
        return self::TIER_LIMITS[$tier->value][$period->value] ?? -1;
    }
}
