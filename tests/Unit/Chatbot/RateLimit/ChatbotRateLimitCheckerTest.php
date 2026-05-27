<?php

declare(strict_types=1);

namespace Tests\App\Unit\Chatbot\RateLimit;

use App\Chatbot\RateLimit\ChatbotRateLimitChecker;
use App\Chatbot\RateLimit\ChatbotRateLimitPeriod;
use App\Chatbot\RateLimit\ChatbotTierResolver;
use App\Chatbot\RateLimit\ChatbotUserTier;
use App\Chatbot\RateLimit\Exception\ChatbotRateLimitExceededException;
use App\Entity\Adherent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Uid\Uuid;

class ChatbotRateLimitCheckerTest extends TestCase
{
    private ArrayAdapter $cache;

    protected function setUp(): void
    {
        $this->cache = new ArrayAdapter();
    }

    public function testUserSimpleConsumesUpToMinuteLimitThenIsRejected(): void
    {
        $checker = $this->buildChecker(ChatbotUserTier::UserSimple);
        $adherent = $this->buildAdherent();

        for ($i = 1; $i <= 3; ++$i) {
            $checker->check($adherent, 'chatbot');
        }

        try {
            $checker->check($adherent, 'chatbot');
            self::fail('Expected ChatbotRateLimitExceededException');
        } catch (ChatbotRateLimitExceededException $exception) {
            self::assertSame(ChatbotRateLimitExceededException::SCOPE_USER, $exception->scope);
            self::assertSame(ChatbotRateLimitPeriod::Minute, $exception->period);
            self::assertSame(ChatbotUserTier::UserSimple, $exception->tier);
            self::assertSame(3, $exception->limit);
            self::assertGreaterThanOrEqual(1, $exception->retryAfter);
        }
    }

    public function testCadreNationalHasHigherMinuteLimit(): void
    {
        $checker = $this->buildChecker(ChatbotUserTier::CadreNational);
        $adherent = $this->buildAdherent();

        for ($i = 1; $i <= 10; ++$i) {
            $checker->check($adherent, 'chatbot');
        }

        $this->expectException(ChatbotRateLimitExceededException::class);
        $checker->check($adherent, 'chatbot');
    }

    public function testCountersAreSeparatedByUser(): void
    {
        $checker = $this->buildChecker(ChatbotUserTier::UserSimple);
        $userA = $this->buildAdherent();
        $userB = $this->buildAdherent();

        for ($i = 1; $i <= 3; ++$i) {
            $checker->check($userA, 'chatbot');
        }

        $checker->check($userB, 'chatbot');
        $checker->check($userB, 'chatbot');

        $this->expectException(ChatbotRateLimitExceededException::class);
        $checker->check($userA, 'chatbot');
    }

    public function testCountersAreSeparatedByAgent(): void
    {
        $checker = $this->buildChecker(ChatbotUserTier::UserSimple);
        $adherent = $this->buildAdherent();

        for ($i = 1; $i <= 3; ++$i) {
            $checker->check($adherent, 'chatbot');
        }

        $checker->check($adherent, 'antiseche');
        $checker->check($adherent, 'antiseche');

        self::assertTrue(true);
    }

    public function testExceptionMessageIncludesTierAndLimit(): void
    {
        $checker = $this->buildChecker(ChatbotUserTier::UserSimple);
        $adherent = $this->buildAdherent();

        for ($i = 1; $i <= 3; ++$i) {
            $checker->check($adherent, 'chatbot');
        }

        try {
            $checker->check($adherent, 'chatbot');
            self::fail('Expected ChatbotRateLimitExceededException');
        } catch (ChatbotRateLimitExceededException $exception) {
            self::assertStringContainsString('Utilisateur simple', $exception->getMessage());
            self::assertStringContainsString('3/min', $exception->getMessage());
        }
    }

    private function buildChecker(ChatbotUserTier $tier): ChatbotRateLimitChecker
    {
        $resolver = $this->createStub(ChatbotTierResolver::class);
        $resolver->method('resolve')->willReturn($tier);

        return new ChatbotRateLimitChecker($resolver, $this->cache);
    }

    private function buildAdherent(): Adherent
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->method('getUuid')->willReturn(Uuid::v4());

        return $adherent;
    }
}
