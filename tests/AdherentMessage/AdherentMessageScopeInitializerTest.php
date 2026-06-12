<?php

declare(strict_types=1);

namespace Tests\App\AdherentMessage;

use App\AdherentMessage\AdherentMessageScopeInitializer;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\MyTeam\Member;
use App\Entity\MyTeam\MyTeam;
use App\Repository\MyTeam\MemberRepository;
use App\Repository\MyTeam\MyTeamRepository;
use App\Scope\Scope;
use App\Scope\ScopeGeneratorResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AdherentMessageScopeInitializerTest extends TestCase
{
    private ScopeGeneratorResolver&MockObject $scopeResolver;
    private (MyTeamRepository&MockObject)|(MyTeamRepository&Stub) $myTeamRepository;
    private (MemberRepository&MockObject)|(MemberRepository&Stub) $memberRepository;
    private (TranslatorInterface&MockObject)|(TranslatorInterface&Stub) $translator;
    private AdherentMessageScopeInitializer $initializer;

    protected function setUp(): void
    {
        $this->scopeResolver = $this->createMock(ScopeGeneratorResolver::class);
        $this->myTeamRepository = $this->createStub(MyTeamRepository::class);
        $this->memberRepository = $this->createStub(MemberRepository::class);
        $this->translator = $this->createStub(TranslatorInterface::class);

        $this->rebuildInitializer();
    }

    private function rebuildInitializer(): void
    {
        $this->initializer = new AdherentMessageScopeInitializer(
            $this->scopeResolver,
            $this->myTeamRepository,
            $this->memberRepository,
            $this->translator,
        );
    }

    public function testInitializeFromScopeDoesNothingWhenNoScope(): void
    {
        $message = new AdherentMessage();

        $this->scopeResolver
            ->expects(self::once())
            ->method('generate')
            ->willReturn(null);

        $this->initializer->initializeFromScope($message);

        self::assertNull($message->getInstanceScope());
        self::assertNull($message->teamOwner);
        self::assertNull($message->getSender());
    }

    public function testInitializeFromScopeSetsSenderForNonNationalScope(): void
    {
        $mainUser = $this->createAdherent('main@example.com', 'Main', 'User');
        $message = new AdherentMessage();
        $scope = $this->createScope('president_departmental_assembly', $mainUser, false);

        $this->scopeResolver
            ->expects(self::once())
            ->method('generate')
            ->willReturn($scope);

        $this->myTeamRepository
            ->method('findOneByAdherentAndScope')
            ->willReturn(null);

        $this->initializer->initializeFromScope($message);

        self::assertSame('president_departmental_assembly', $message->getInstanceScope());
        self::assertSame($mainUser, $message->teamOwner);
        self::assertSame($mainUser, $message->getSender());
    }

    public function testInitializeFromScopeKeepsSenderNullForNationalScope(): void
    {
        $mainUser = $this->createAdherent('national@example.com', 'National', 'User');
        $message = new AdherentMessage();
        $scope = $this->createScope('national', $mainUser, true);

        $this->scopeResolver
            ->expects(self::once())
            ->method('generate')
            ->willReturn($scope);

        $this->myTeamRepository
            ->method('findOneByAdherentAndScope')
            ->willReturn(null);

        $this->initializer->initializeFromScope($message);

        self::assertSame('national', $message->getInstanceScope());
        self::assertSame($mainUser, $message->teamOwner);
        self::assertNull($message->getSender());
    }

    public function testInitializeFromScopeWithForceResetClearsExistingValues(): void
    {
        $oldOwner = $this->createAdherent('old@example.com', 'Old', 'Owner');
        $oldSender = $this->createAdherent('oldsender@example.com', 'Old', 'Sender');
        $newUser = $this->createAdherent('new@example.com', 'New', 'User');

        $message = new AdherentMessage();
        $message->setInstanceScope('old_scope');
        $message->teamOwner = $oldOwner;
        $message->senderRole = 'Old Role';
        $message->setSender($oldSender);

        $scope = $this->createScope('new_scope', $newUser, false);

        $this->scopeResolver
            ->expects(self::once())
            ->method('generate')
            ->willReturn($scope);

        $this->myTeamRepository
            ->method('findOneByAdherentAndScope')
            ->willReturn(null);

        $this->initializer->initializeFromScope($message, forceReset: true);

        self::assertSame('new_scope', $message->getInstanceScope());
        self::assertSame($newUser, $message->teamOwner);
        // sender should be reset and set to newUser (non-national scope)
        self::assertSame($newUser, $message->getSender());
        // senderRole is recalculated via updateSenderDataFromScope
        self::assertSame('Role', $message->senderRole);
    }

    public function testInitializeFromScopePreservesExistingValuesWithoutForceReset(): void
    {
        $existingOwner = $this->createAdherent('existing@example.com', 'Existing', 'Owner');
        $newUser = $this->createAdherent('new@example.com', 'New', 'User');

        $message = new AdherentMessage();
        $message->setInstanceScope('existing_scope');
        $message->teamOwner = $existingOwner;

        $scope = $this->createScope('new_scope', $newUser, false);

        $this->scopeResolver
            ->expects(self::once())
            ->method('generate')
            ->willReturn($scope);

        $this->myTeamRepository
            ->method('findOneByAdherentAndScope')
            ->willReturn(null);

        $this->initializer->initializeFromScope($message, forceReset: false);

        // Values should NOT be changed because they were already set
        self::assertSame('existing_scope', $message->getInstanceScope());
        self::assertSame($existingOwner, $message->teamOwner);
    }

    public function testInitializeFromScopeSetsSenderRoleForDelegation(): void
    {
        $teamOwner = $this->createAdherent('owner@example.com', 'Team', 'Owner');
        $sender = $this->createAdherent('sender@example.com', 'Team', 'Member', 'male');

        $message = new AdherentMessage();
        $message->setSender($sender);

        $scope = $this->createScope('president_departmental_assembly', $teamOwner, false);

        $team = $this->createStub(MyTeam::class);
        $member = $this->createStub(Member::class);
        $member->method('getRole')->willReturn('delegate');
        $member->method('getAdherent')->willReturn($sender);
        $this->myTeamRepository = $this->createMock(MyTeamRepository::class);
        $this->memberRepository = $this->createMock(MemberRepository::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->rebuildInitializer();

        $this->scopeResolver
            ->expects(self::once())
            ->method('generate')
            ->willReturn($scope);

        $this->myTeamRepository
            ->expects(self::once())
            ->method('findOneByAdherentAndScope')
            ->with($teamOwner, 'president_departmental_assembly')
            ->willReturn($team);

        $this->memberRepository
            ->expects(self::once())
            ->method('findMemberInTeam')
            ->with($team, $sender)
            ->willReturn($member);

        $this->translator
            ->expects(self::once())
            ->method('trans')
            ->with('my_team_member.role.delegate', ['gender' => 'male'])
            ->willReturn('Délégué');

        $this->initializer->initializeFromScope($message);

        self::assertSame('Délégué', $message->senderRole);
    }

    public function testInitializeFromScopeUsesFallbackRoleIfTranslationNotFound(): void
    {
        $teamOwner = $this->createAdherent('owner@example.com', 'Team', 'Owner');
        $sender = $this->createAdherent('sender@example.com', 'Team', 'Member');

        $message = new AdherentMessage();
        $message->setSender($sender);

        $scope = $this->createScope('president_departmental_assembly', $teamOwner, false);

        $team = $this->createStub(MyTeam::class);
        $member = $this->createStub(Member::class);
        $member->method('getRole')->willReturn('custom_role');
        $member->method('getAdherent')->willReturn($sender);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->rebuildInitializer();

        $this->scopeResolver
            ->expects(self::once())
            ->method('generate')
            ->willReturn($scope);

        $this->myTeamRepository
            ->method('findOneByAdherentAndScope')
            ->willReturn($team);

        $this->memberRepository
            ->method('findMemberInTeam')
            ->willReturn($member);

        // Translator returns the key itself (no translation found)
        $this->translator
            ->expects(self::once())
            ->method('trans')
            ->with('my_team_member.role.custom_role', ['gender' => null])
            ->willReturn('my_team_member.role.custom_role');

        $this->initializer->initializeFromScope($message);

        // Should fall back to raw role name
        self::assertSame('custom_role', $message->senderRole);
    }

    private function createAdherent(string $email, string $firstName, string $lastName, ?string $gender = null): Adherent&Stub
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->method('getEmailAddress')->willReturn($email);
        $adherent->method('getFirstName')->willReturn($firstName);
        $adherent->method('getLastName')->willReturn($lastName);
        $adherent->method('getFullName')->willReturn($firstName.' '.$lastName);
        $adherent->method('getGender')->willReturn($gender);

        return $adherent;
    }

    private function createScope(string $code, Adherent $mainUser, bool $isNational): Scope&Stub
    {
        $scope = $this->createStub(Scope::class);
        $scope->method('getMainCode')->willReturn($code);
        $scope->method('getMainUser')->willReturn($mainUser);
        $scope->method('isNational')->willReturn($isNational);
        $scope->method('getScopeInstance')->willReturn(null);
        $scope->method('getMainRoleName')->willReturn('Role');
        $scope->method('getAttribute')->willReturn(null);
        $scope->method('getZoneNames')->willReturn([]);

        return $scope;
    }
}
