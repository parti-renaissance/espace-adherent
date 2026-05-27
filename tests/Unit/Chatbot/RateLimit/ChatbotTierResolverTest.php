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

    public function testNationalRoleReturnsCadreNational(): void
    {
        $adherent = $this->makeAdherent(['ROLE_NATIONAL'], []);

        self::assertSame(ChatbotUserTier::CadreNational, $this->resolver->resolve($adherent));
    }

    public function testDeputyReturnsCadreNational(): void
    {
        $adherent = $this->makeAdherent(['ROLE_DEPUTY'], []);

        self::assertSame(ChatbotUserTier::CadreNational, $this->resolver->resolve($adherent));
    }

    public function testSenatorReturnsCadreNational(): void
    {
        $adherent = $this->makeAdherent(['ROLE_SENATOR'], []);

        self::assertSame(ChatbotUserTier::CadreNational, $this->resolver->resolve($adherent));
    }

    public function testFdeCoordinatorReturnsCadreNational(): void
    {
        $adherent = $this->makeAdherent(['ROLE_FDE_COORDINATOR'], []);

        self::assertSame(ChatbotUserTier::CadreNational, $this->resolver->resolve($adherent));
    }

    public function testRegionalCoordinatorReturnsCadreLocal(): void
    {
        $adherent = $this->makeAdherent(['ROLE_REGIONAL_COORDINATOR'], []);

        self::assertSame(ChatbotUserTier::CadreLocal, $this->resolver->resolve($adherent));
    }

    public function testHostReturnsCadreLocal(): void
    {
        $adherent = $this->makeAdherent(['ROLE_HOST'], []);

        self::assertSame(ChatbotUserTier::CadreLocal, $this->resolver->resolve($adherent));
    }

    public function testAnimatorReturnsCadreLocal(): void
    {
        $adherent = $this->makeAdherent(['ROLE_ANIMATOR'], []);

        self::assertSame(ChatbotUserTier::CadreLocal, $this->resolver->resolve($adherent));
    }

    public function testPresidentDepartmentalAssemblyReturnsCadreLocal(): void
    {
        $adherent = $this->makeAdherent(['ROLE_PRESIDENT_DEPARTMENTAL_ASSEMBLY'], []);

        self::assertSame(ChatbotUserTier::CadreLocal, $this->resolver->resolve($adherent));
    }

    public function testRegionalDelegateReturnsCadreLocal(): void
    {
        $adherent = $this->makeAdherent(['ROLE_REGIONAL_DELEGATE'], []);

        self::assertSame(ChatbotUserTier::CadreLocal, $this->resolver->resolve($adherent));
    }

    public function testAdherentAJourReturnsAdherentAJour(): void
    {
        $year = date('Y');
        $adherent = $this->makeAdherent([], ['adherent:a_jour_'.$year]);

        self::assertSame(ChatbotUserTier::AdherentAJour, $this->resolver->resolve($adherent));
    }

    public function testAdherentTagButNotUpToDateReturnsAdherent(): void
    {
        $adherent = $this->makeAdherent([], ['adherent']);

        self::assertSame(ChatbotUserTier::Adherent, $this->resolver->resolve($adherent));
    }

    public function testAdherentNotUpToDateTagReturnsAdherent(): void
    {
        $adherent = $this->makeAdherent([], ['adherent:plus_a_jour']);

        self::assertSame(ChatbotUserTier::Adherent, $this->resolver->resolve($adherent));
    }

    public function testSympathisantReturnsSympathisant(): void
    {
        $adherent = $this->makeAdherent([], ['sympathisant']);

        self::assertSame(ChatbotUserTier::Sympathisant, $this->resolver->resolve($adherent));
    }

    public function testSympathisantSubtagReturnsSympathisant(): void
    {
        $adherent = $this->makeAdherent([], ['sympathisant:compte_em']);

        self::assertSame(ChatbotUserTier::Sympathisant, $this->resolver->resolve($adherent));
    }

    public function testNoTagsNoRolesReturnsUserSimple(): void
    {
        $adherent = $this->makeAdherent([], []);

        self::assertSame(ChatbotUserTier::UserSimple, $this->resolver->resolve($adherent));
    }

    public function testCadreNationalBeatsAdherentAJour(): void
    {
        $year = date('Y');
        $adherent = $this->makeAdherent(['ROLE_NATIONAL'], ['adherent:a_jour_'.$year]);

        self::assertSame(ChatbotUserTier::CadreNational, $this->resolver->resolve($adherent));
    }

    public function testCadreLocalBeatsAdherent(): void
    {
        $adherent = $this->makeAdherent(['ROLE_HOST'], ['adherent']);

        self::assertSame(ChatbotUserTier::CadreLocal, $this->resolver->resolve($adherent));
    }

    public function testCadreNationalBeatsCadreLocal(): void
    {
        $adherent = $this->makeAdherent(['ROLE_DEPUTY', 'ROLE_HOST'], []);

        self::assertSame(ChatbotUserTier::CadreNational, $this->resolver->resolve($adherent));
    }

    public function testAdherentAJourBeatsAdherent(): void
    {
        $year = date('Y');
        $adherent = $this->makeAdherent([], ['adherent', 'adherent:a_jour_'.$year]);

        self::assertSame(ChatbotUserTier::AdherentAJour, $this->resolver->resolve($adherent));
    }

    public function testAdherentBeatsSympathisant(): void
    {
        $adherent = $this->makeAdherent([], ['sympathisant', 'adherent']);

        self::assertSame(ChatbotUserTier::Adherent, $this->resolver->resolve($adherent));
    }

    /**
     * @param string[] $extraRoles
     * @param string[] $tags
     */
    private function makeAdherent(array $extraRoles, array $tags): Adherent
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->method('getRoles')->willReturn(array_merge(['ROLE_USER'], $extraRoles));
        $adherent->method('hasTag')->willReturnCallback(
            function (string $searchTag) use ($tags): bool {
                foreach ($tags as $storedTag) {
                    if (str_starts_with($storedTag, $searchTag)) {
                        return true;
                    }
                }

                return false;
            }
        );

        return $adherent;
    }
}
