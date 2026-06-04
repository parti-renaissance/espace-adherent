<?php

declare(strict_types=1);

namespace Tests\App\Unit\Chatbot\RateLimit;

use App\Chatbot\RateLimit\ChatbotRateLimitPeriod;
use App\Chatbot\RateLimit\ChatbotUserTier;
use App\Chatbot\RateLimit\Exception\ChatbotRateLimitExceededException;
use PHPUnit\Framework\TestCase;

class ChatbotRateLimitExceededExceptionTest extends TestCase
{
    public function testMinuteMessage(): void
    {
        $exception = ChatbotRateLimitExceededException::forTier(ChatbotUserTier::Adherent, ChatbotRateLimitPeriod::Minute, 5, 4);

        self::assertStringContainsString('limite de questions pour cette minute', $exception->getMessage());
        self::assertStringContainsString('Patientez quelques instants', $exception->getMessage());
    }

    public function testHourMessage(): void
    {
        $exception = ChatbotRateLimitExceededException::forTier(ChatbotUserTier::Adherent, ChatbotRateLimitPeriod::Hour, 60, 20);

        self::assertStringContainsString('limite de questions pour cette heure', $exception->getMessage());
        self::assertStringContainsString('repassez d\'ici peu', $exception->getMessage());
    }

    public function testDayMessageForAdherentHasNoUpsell(): void
    {
        $exception = ChatbotRateLimitExceededException::forTier(ChatbotUserTier::Adherent, ChatbotRateLimitPeriod::Day, 3600, 40);

        self::assertStringContainsString('pour aujourd\'hui. Le compteur repart demain.', $exception->getMessage());
        self::assertStringNotContainsString('Envie d\'un accès plus large', $exception->getMessage());
    }

    public function testDayMessageForLowTiersHasUpsell(): void
    {
        foreach ([ChatbotUserTier::Public, ChatbotUserTier::Contact, ChatbotUserTier::Sympathisant] as $tier) {
            $exception = ChatbotRateLimitExceededException::forTier($tier, ChatbotRateLimitPeriod::Day, 3600, 8);

            self::assertStringContainsString('Envie d\'un accès plus large', $exception->getMessage());
            self::assertStringContainsString('les adhérents disposent d\'un quota nettement plus généreux', mb_strtolower($exception->getMessage()));
        }
    }

    public function testGlobalMessage(): void
    {
        $exception = ChatbotRateLimitExceededException::forGlobal(ChatbotRateLimitPeriod::Day, 3600, 2000);

        self::assertStringContainsString('victime de son succès', $exception->getMessage());
        self::assertStringContainsString('Le service repart demain.', $exception->getMessage());
        self::assertSame(ChatbotRateLimitExceededException::SCOPE_GLOBAL, $exception->scope);
    }

    public function testBlockedTierMessage(): void
    {
        $exception = ChatbotRateLimitExceededException::forTier(ChatbotUserTier::Public, ChatbotRateLimitPeriod::Minute, 1, 0);

        self::assertStringContainsString('n\'est pas autorisé pour votre profil', $exception->getMessage());
    }
}
