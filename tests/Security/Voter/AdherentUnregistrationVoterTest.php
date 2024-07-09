<?php

namespace Tests\App\Security\Voter;

use App\Collection\CommitteeMembershipCollection;
use App\Entity\Adherent;
use App\Entity\CommitteeMembership;
use App\Security\Voter\AdherentUnregistrationVoter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AdherentUnregistrationVoterTest extends TestCase
{
    private ?AdherentUnregistrationVoter $voter = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->voter = new AdherentUnregistrationVoter();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->voter = null;
    }

    public function provideAnonymousCases(): iterable
    {
        yield [false, true, AdherentUnregistrationVoter::PERMISSION_UNREGISTER];
    }

    public static function provideBasicAdherentCases(): iterable
    {
        yield [true];
        yield [false];
    }

    public function testAdherentIsGrantedIfUser(): void
    {
        $adherent = $this->createAdherentMock();

        $adherent->expects($this->once())
            ->method('isUser')
            ->willReturn(true)
        ;

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($this->createTokenMock(), $adherent, [AdherentUnregistrationVoter::PERMISSION_UNREGISTER])
        );
    }

    public static function provideUserCases(): iterable
    {
        yield [true];
        yield [false];
    }

    #[DataProvider('provideAdherentWithRolesCases')]
    public function testAdherentIsNotGrantedIfRole(
        bool $isPresidentDepartmentalAssembly,
        bool $isAnimator,
        bool $isDeputy,
        bool $isRegionalDelegate,
        bool $hasCommitteeCandidacy
    ): void {
        $adherent = $this->createAdherentMock();

        $adherent->expects($this->any())
            ->method('isPresidentDepartmentalAssembly')
            ->willReturn($isPresidentDepartmentalAssembly)
        ;

        $adherent->expects($this->any())
            ->method('isAnimator')
            ->willReturn($isAnimator)
        ;

        $adherent->expects($this->any())
            ->method('isDeputy')
            ->willReturn($isDeputy)
        ;

        $adherent->expects($this->any())
            ->method('isRegionalDelegate')
            ->willReturn($isRegionalDelegate)
        ;

        $memberships = $this->createMock(CommitteeMembershipCollection::class);
        $memberships->expects($this->any())
            ->method('getCommitteeCandidacyMembership')
            ->willReturn($hasCommitteeCandidacy ? $this->createMock(CommitteeMembership::class) : null)
        ;
        $adherent->expects($this->any())
            ->method('getMemberships')
            ->willReturn($memberships)
        ;

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->createTokenMock(), $adherent, [AdherentUnregistrationVoter::PERMISSION_UNREGISTER])
        );
    }

    public static function provideAdherentWithRolesCases(): iterable
    {
        yield [true, false, false, false, false, false];
        yield [false, true, false, false, false, false];
        yield [false, false, true, false, false, false];
        yield [false, false, false, true, false, false];
        yield [false, false, false, false, true, false];
        yield [true, true, true, true, true, true];
    }

    private function createAdherentMock(): Adherent
    {
        return $this->createMock(Adherent::class);
    }

    private function createTokenMock(): TokenInterface
    {
        return $this->createMock(TokenInterface::class);
    }
}
