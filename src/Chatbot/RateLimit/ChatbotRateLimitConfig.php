<?php

declare(strict_types=1);

namespace App\Chatbot\RateLimit;

/**
 * Conventions :
 *   - int > 0 : limite normale
 *   - 0       : bloqué (toujours refusé)
 *   - null    : illimité (limiteur non évalué)
 */
final class ChatbotRateLimitConfig
{
    /** @var array<value-of<ChatbotRateLimitPeriod>, int|null> */
    private const GLOBAL_LIMITS = [
        'minute' => 150,
        'hour' => 2500,
        'day' => 2000,
    ];

    /** @var array<value-of<ChatbotUserTier>, array<value-of<ChatbotRateLimitPeriod>, int|null>> */
    private const TIER_LIMITS = [
        'public' => ['minute' => 2, 'hour' => 5, 'day' => 8],
        'user_simple' => ['minute' => 3, 'hour' => 10, 'day' => 15],
        'sympathisant' => ['minute' => 3, 'hour' => 15, 'day' => 25],
        'adherent' => ['minute' => 4, 'hour' => 20, 'day' => 40],
        'adherent_a_jour' => ['minute' => 5, 'hour' => 30, 'day' => 60],
        'cadre_local' => ['minute' => 6, 'hour' => 40, 'day' => 100],
        'cadre_national' => ['minute' => 10, 'hour' => 60, 'day' => 200],
    ];

    public static function getGlobalLimit(ChatbotRateLimitPeriod $period): ?int
    {
        return self::GLOBAL_LIMITS[$period->value] ?? null;
    }

    public static function getTierLimit(ChatbotUserTier $tier, ChatbotRateLimitPeriod $period): ?int
    {
        return self::TIER_LIMITS[$tier->value][$period->value] ?? null;
    }
}
