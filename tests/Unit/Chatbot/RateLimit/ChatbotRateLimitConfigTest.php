<?php

declare(strict_types=1);

namespace Tests\App\Unit\Chatbot\RateLimit;

use App\Chatbot\RateLimit\ChatbotRateLimitConfig;
use App\Chatbot\RateLimit\ChatbotRateLimitPeriod;
use App\Chatbot\RateLimit\ChatbotUserTier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ChatbotRateLimitConfigTest extends TestCase
{
    #[DataProvider('provideTierLimits')]
    public function testGetTierLimit(ChatbotUserTier $tier, ChatbotRateLimitPeriod $period, int $expected): void
    {
        self::assertSame($expected, ChatbotRateLimitConfig::getTierLimit($tier, $period));
    }

    /**
     * @return iterable<string, array{ChatbotUserTier, ChatbotRateLimitPeriod, int}>
     */
    public static function provideTierLimits(): iterable
    {
        yield 'public minute' => [ChatbotUserTier::Public, ChatbotRateLimitPeriod::Minute, 0];
        yield 'public hour' => [ChatbotUserTier::Public, ChatbotRateLimitPeriod::Hour, 0];
        yield 'public day' => [ChatbotUserTier::Public, ChatbotRateLimitPeriod::Day, 0];

        yield 'contact minute' => [ChatbotUserTier::Contact, ChatbotRateLimitPeriod::Minute, 3];
        yield 'contact hour' => [ChatbotUserTier::Contact, ChatbotRateLimitPeriod::Hour, 5];
        yield 'contact day' => [ChatbotUserTier::Contact, ChatbotRateLimitPeriod::Day, 10];

        yield 'sympathisant minute' => [ChatbotUserTier::Sympathisant, ChatbotRateLimitPeriod::Minute, 3];
        yield 'sympathisant hour' => [ChatbotUserTier::Sympathisant, ChatbotRateLimitPeriod::Hour, 5];
        yield 'sympathisant day' => [ChatbotUserTier::Sympathisant, ChatbotRateLimitPeriod::Day, 10];

        yield 'adherent minute' => [ChatbotUserTier::Adherent, ChatbotRateLimitPeriod::Minute, 3];
        yield 'adherent hour' => [ChatbotUserTier::Adherent, ChatbotRateLimitPeriod::Hour, 5];
        yield 'adherent day' => [ChatbotUserTier::Adherent, ChatbotRateLimitPeriod::Day, 20];

        yield 'adherent_a_jour minute' => [ChatbotUserTier::AdherentAJour, ChatbotRateLimitPeriod::Minute, 3];
        yield 'adherent_a_jour hour' => [ChatbotUserTier::AdherentAJour, ChatbotRateLimitPeriod::Hour, 5];
        yield 'adherent_a_jour day' => [ChatbotUserTier::AdherentAJour, ChatbotRateLimitPeriod::Day, 20];

        yield 'cadre minute' => [ChatbotUserTier::Cadre, ChatbotRateLimitPeriod::Minute, 3];
        yield 'cadre hour' => [ChatbotUserTier::Cadre, ChatbotRateLimitPeriod::Hour, 10];
        yield 'cadre day' => [ChatbotUserTier::Cadre, ChatbotRateLimitPeriod::Day, 50];
    }

    #[DataProvider('provideGlobalLimits')]
    public function testGetGlobalLimit(ChatbotRateLimitPeriod $period, int $expected): void
    {
        self::assertSame($expected, ChatbotRateLimitConfig::getGlobalLimit($period));
    }

    /**
     * @return iterable<string, array{ChatbotRateLimitPeriod, int}>
     */
    public static function provideGlobalLimits(): iterable
    {
        yield 'day' => [ChatbotRateLimitPeriod::Day, 9000];
        yield 'minute is unbounded' => [ChatbotRateLimitPeriod::Minute, -1];
        yield 'hour is unbounded' => [ChatbotRateLimitPeriod::Hour, -1];
    }
}
