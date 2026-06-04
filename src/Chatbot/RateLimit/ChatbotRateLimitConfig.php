<?php

declare(strict_types=1);

namespace App\Chatbot\RateLimit;

final class ChatbotRateLimitConfig
{
    /** @var array<value-of<ChatbotRateLimitPeriod>, int> */
    private const array GLOBAL_LIMITS = [
        'minute' => 150,
        'hour' => 2500,
        'day' => 3000,
    ];

    /** @var array<value-of<ChatbotUserTier>, array<value-of<ChatbotRateLimitPeriod>, int>> */
    private const array TIER_LIMITS = [
        'public' => ['minute' => 0, 'hour' => 0, 'day' => 0],
        'contact' => ['minute' => 3, 'hour' => 10, 'day' => 15],
        'sympathisant' => ['minute' => 3, 'hour' => 15, 'day' => 25],
        'adherent' => ['minute' => 4, 'hour' => 20, 'day' => 40],
        'adherent_a_jour' => ['minute' => 5, 'hour' => 30, 'day' => 60],
        'cadre' => ['minute' => 10, 'hour' => 60, 'day' => 200],
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
