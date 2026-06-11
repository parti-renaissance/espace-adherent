<?php

declare(strict_types=1);

namespace Tests\App\Unit\Chatbot\RateLimit;

use App\Adherent\AdherentLevel;
use App\Chatbot\RateLimit\ChatbotTierResolver;
use App\Chatbot\RateLimit\ChatbotUserTier;
use App\Entity\Adherent;
use PHPUnit\Framework\TestCase;

class ChatbotTierResolverTest extends TestCase
{
    private ChatbotTierResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new ChatbotTierResolver();
    }

    public function testNullAdherentReturnsPublic(): void
    {
        self::assertSame(ChatbotUserTier::Public, $this->resolver->resolve(null));
    }

    public function testCadreReturnsCadre(): void
    {
        self::assertSame(ChatbotUserTier::Cadre, $this->resolver->resolve($this->makeAdherent(cadre: true)));
    }

    public function testCadreBeatsMembershipLevel(): void
    {
        $adherent = $this->makeAdherent(cadre: true, level: AdherentLevel::ADHERENT_A_JOUR);

        self::assertSame(ChatbotUserTier::Cadre, $this->resolver->resolve($adherent));
    }

    public function testActiveMembershipReturnsAdherentAJour(): void
    {
        self::assertSame(ChatbotUserTier::AdherentAJour, $this->resolver->resolve($this->makeAdherent(level: AdherentLevel::ADHERENT_A_JOUR)));
    }

    public function testAdherentReturnsAdherent(): void
    {
        self::assertSame(ChatbotUserTier::Adherent, $this->resolver->resolve($this->makeAdherent(level: AdherentLevel::ADHERENT)));
    }

    public function testMembreReturnsSympathisant(): void
    {
        self::assertSame(ChatbotUserTier::Sympathisant, $this->resolver->resolve($this->makeAdherent(level: AdherentLevel::MEMBRE)));
    }

    public function testUserReturnsContact(): void
    {
        self::assertSame(ChatbotUserTier::Contact, $this->resolver->resolve($this->makeAdherent(level: AdherentLevel::USER)));
    }

    public function testContactReturnsContact(): void
    {
        self::assertSame(ChatbotUserTier::Contact, $this->resolver->resolve($this->makeAdherent(level: AdherentLevel::CONTACT)));
    }

    private function makeAdherent(bool $cadre = false, AdherentLevel $level = AdherentLevel::CONTACT): Adherent
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->method('isCadre')->willReturn($cadre);
        $adherent->method('getLevel')->willReturn($level);

        return $adherent;
    }
}
