<?php

declare(strict_types=1);

namespace Tests\App\Unit\Chatbot\RateLimit;

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

    public function testResponsibilityRoleReturnsCadre(): void
    {
        foreach (['ROLE_ANIMATOR', 'ROLE_HOST', 'ROLE_DEPUTY', 'ROLE_NATIONAL', 'ROLE_CORRESPONDENT'] as $role) {
            self::assertSame(ChatbotUserTier::Cadre, $this->resolver->resolve($this->makeAdherent(roles: [$role])));
        }
    }

    public function testDelegatedAccessReturnsCadre(): void
    {
        self::assertSame(ChatbotUserTier::Cadre, $this->resolver->resolve($this->makeAdherent(roles: ['ROLE_DELEGATED_ANIMATOR'])));
    }

    public function testTechnicalRoleIsNotCadre(): void
    {
        $adherent = $this->makeAdherent(roles: ['ROLE_PAP_USER', 'ROLE_CANARY_TESTER'], activeMembership: true);

        self::assertSame(ChatbotUserTier::AdherentAJour, $this->resolver->resolve($adherent));
    }

    public function testActiveMembershipReturnsAdherentAJour(): void
    {
        self::assertSame(ChatbotUserTier::AdherentAJour, $this->resolver->resolve($this->makeAdherent(activeMembership: true)));
    }

    public function testAdherentReturnsAdherent(): void
    {
        self::assertSame(ChatbotUserTier::Adherent, $this->resolver->resolve($this->makeAdherent(renaissanceAdherent: true)));
    }

    public function testSympathizerReturnsSympathisant(): void
    {
        self::assertSame(ChatbotUserTier::Sympathisant, $this->resolver->resolve($this->makeAdherent(sympathizer: true)));
    }

    public function testNoMembershipReturnsContact(): void
    {
        self::assertSame(ChatbotUserTier::Contact, $this->resolver->resolve($this->makeAdherent()));
    }

    public function testCadreBeatsActiveMembership(): void
    {
        $adherent = $this->makeAdherent(roles: ['ROLE_ANIMATOR'], activeMembership: true, renaissanceAdherent: true);

        self::assertSame(ChatbotUserTier::Cadre, $this->resolver->resolve($adherent));
    }

    public function testActiveMembershipBeatsAdherent(): void
    {
        $adherent = $this->makeAdherent(activeMembership: true, renaissanceAdherent: true);

        self::assertSame(ChatbotUserTier::AdherentAJour, $this->resolver->resolve($adherent));
    }

    /**
     * @param string[] $roles
     */
    private function makeAdherent(
        array $roles = [],
        bool $activeMembership = false,
        bool $renaissanceAdherent = false,
        bool $sympathizer = false,
    ): Adherent {
        $adherent = $this->createStub(Adherent::class);
        $adherent->method('getRoles')->willReturn(array_merge(['ROLE_USER'], $roles));
        $adherent->method('hasActiveMembership')->willReturn($activeMembership);
        $adherent->method('isRenaissanceAdherent')->willReturn($renaissanceAdherent);
        $adherent->method('isRenaissanceSympathizer')->willReturn($sympathizer);

        return $adherent;
    }
}
