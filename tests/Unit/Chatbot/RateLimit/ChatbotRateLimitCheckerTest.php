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

    public function testContactConsumesUpToMinuteLimitThenIsRejected(): void
    {
        $checker = $this->buildChecker(ChatbotUserTier::Contact);
        $adherent = $this->buildAdherent();

        for ($i = 1; $i <= 3; ++$i) {
            $checker->check($adherent, 'antiseche');
        }

        try {
            $checker->check($adherent, 'antiseche');
            self::fail('Expected ChatbotRateLimitExceededException');
        } catch (ChatbotRateLimitExceededException $exception) {
            self::assertSame(ChatbotRateLimitExceededException::SCOPE_USER, $exception->scope);
            self::assertSame(ChatbotRateLimitPeriod::Minute, $exception->period);
            self::assertSame(ChatbotUserTier::Contact, $exception->tier);
            self::assertSame(3, $exception->limit);
            self::assertGreaterThanOrEqual(1, $exception->retryAfter);
        }
    }

    public function testCountersAreSeparatedByUser(): void
    {
        $checker = $this->buildChecker(ChatbotUserTier::Contact);
        $userA = $this->buildAdherent();
        $userB = $this->buildAdherent();

        for ($i = 1; $i <= 3; ++$i) {
            $checker->check($userA, 'antiseche');
        }

        $checker->check($userB, 'antiseche');
        $checker->check($userB, 'antiseche');

        $this->expectException(ChatbotRateLimitExceededException::class);
        $checker->check($userA, 'antiseche');
    }

    public function testChatbotAgentIsNotRateLimited(): void
    {
        $checker = $this->buildChecker(ChatbotUserTier::Contact);
        $adherent = $this->buildAdherent();

        for ($i = 1; $i <= 200; ++$i) {
            $checker->check($adherent, 'chatbot');
        }

        $this->expectNotToPerformAssertions();
    }

    public function testMinuteLimitMessage(): void
    {
        $checker = $this->buildChecker(ChatbotUserTier::Contact);
        $adherent = $this->buildAdherent();

        for ($i = 1; $i <= 3; ++$i) {
            $checker->check($adherent, 'antiseche');
        }

        try {
            $checker->check($adherent, 'antiseche');
            self::fail('Expected ChatbotRateLimitExceededException');
        } catch (ChatbotRateLimitExceededException $exception) {
            self::assertStringContainsString('limite de questions pour cette minute', $exception->getMessage());
        }
    }

    public function testCadreIsUnlimitedOutsideProduction(): void
    {
        $checker = $this->buildChecker(ChatbotUserTier::Cadre, 'staging');
        $adherent = $this->buildAdherent();

        for ($i = 1; $i <= 60; ++$i) {
            $checker->check($adherent, 'antiseche');
        }

        $this->expectNotToPerformAssertions();
    }

    public function testCadreRemainsLimitedInProduction(): void
    {
        $checker = $this->buildChecker(ChatbotUserTier::Cadre, 'production');
        $adherent = $this->buildAdherent();

        for ($i = 1; $i <= 3; ++$i) {
            $checker->check($adherent, 'antiseche');
        }

        $this->expectException(ChatbotRateLimitExceededException::class);
        $checker->check($adherent, 'antiseche');
    }

    public function testNonCadreRemainsLimitedOutsideProduction(): void
    {
        $checker = $this->buildChecker(ChatbotUserTier::Contact, 'staging');
        $adherent = $this->buildAdherent();

        for ($i = 1; $i <= 3; ++$i) {
            $checker->check($adherent, 'antiseche');
        }

        $this->expectException(ChatbotRateLimitExceededException::class);
        $checker->check($adherent, 'antiseche');
    }

    private function buildChecker(ChatbotUserTier $tier, string $appEnvironment = 'production'): ChatbotRateLimitChecker
    {
        $resolver = $this->createStub(ChatbotTierResolver::class);
        $resolver->method('resolve')->willReturn($tier);

        return new ChatbotRateLimitChecker($resolver, $this->cache, $appEnvironment);
    }

    private function buildAdherent(): Adherent
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->method('getUuid')->willReturn(Uuid::v4());

        return $adherent;
    }
}
