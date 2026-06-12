<?php

declare(strict_types=1);

namespace Tests\App\Security\Voter;

use App\Collection\ZoneCollection;
use App\Entity\Adherent;
use App\Entity\Agora;
use App\Entity\AgoraMembership;
use App\Entity\AuthorInstanceInterface;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\Event\Event;
use App\Entity\Geo\Zone;
use App\Entity\Projection\ManagedUser;
use App\Entity\ZoneableEntityInterface;
use App\Repository\Geo\ZoneRepository;
use App\Scope\Scope;
use App\Scope\ScopeGeneratorResolver;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\ManageZoneableItemVoter;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Component\Uid\Uuid;

#[Group('unit')]
class ManageZoneableItemVoterTest extends AbstractAdherentVoterTestCase
{
    private const PERMISSION = 'MANAGE_ZONEABLE_ITEM__FOR_SCOPE';

    private const COMMITTEE_UUID_IN = '11111111-1111-1111-1111-111111111111';
    private const COMMITTEE_UUID_OUT = '22222222-2222-2222-2222-222222222222';
    private const AGORA_UUID_IN = '33333333-3333-3333-3333-333333333333';
    private const AGORA_UUID_OUT = '44444444-4444-4444-4444-444444444444';

    private ScopeGeneratorResolver&MockObject $scopeGeneratorResolver;
    private ZoneRepository&MockObject $zoneRepository;

    protected function setUp(): void
    {
        $this->scopeGeneratorResolver = $this->createMock(ScopeGeneratorResolver::class);
        $this->zoneRepository = $this->createMock(ZoneRepository::class);

        parent::setUp();
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new ManageZoneableItemVoter($this->scopeGeneratorResolver, $this->zoneRepository);
    }

    public static function provideAnonymousCases(): iterable
    {
        yield [false, true, self::PERMISSION, function (self $_this): Adherent {
            $_this->scopeGeneratorResolver->expects($_this->never())->method('generate');
            $_this->zoneRepository->expects($_this->never())->method('isInZones');

            return $_this->createAdherentSubject();
        }];
    }

    public function testVoteWithNationalScopeIsGranted(): void
    {
        $adherent = new Adherent();
        $subject = $this->createAdherentSubject();

        $scope = $this->createScopeStub(isNational: true);
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $this->assertGrantedForAdherent(true, true, $adherent, self::PERMISSION, $subject);
    }

    public function testVoteWithAuthorInstanceMatchIsGranted(): void
    {
        $adherent = new Adherent();
        $subject = $this->createAuthorInstanceSubject('animator:abc');

        $scope = $this->createScopeStub(instanceKey: 'animator:abc');
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $this->assertGrantedForAdherent(true, true, $adherent, self::PERMISSION, $subject);
    }

    public function testVoteWithAuthorInstanceMismatchAndEmptyScopeIsDenied(): void
    {
        $adherent = new Adherent();
        $subject = $this->createAuthorInstanceSubject('animator:abc');

        $scope = $this->createScopeStub(instanceKey: 'animator:other');
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $this->assertGrantedForAdherent(false, true, $adherent, self::PERMISSION, $subject);
    }

    public function testVoteEventWithCommitteeMatchIsGranted(): void
    {
        $adherent = new Adherent();
        $event = new Event();
        $event->setCommittee($this->createCommitteeStub(self::COMMITTEE_UUID_IN));

        $scope = $this->createScopeStub(committeeUuids: [self::COMMITTEE_UUID_IN]);
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $this->assertGrantedForAdherent(true, true, $adherent, self::PERMISSION, $event);
    }

    public function testVoteEventWithCommitteeMismatchAndNoZoneIsDenied(): void
    {
        $adherent = new Adherent();
        $event = new Event();
        $event->setCommittee($this->createCommitteeStub(self::COMMITTEE_UUID_OUT));

        $scope = $this->createScopeStub(committeeUuids: [self::COMMITTEE_UUID_IN]);
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $this->assertGrantedForAdherent(false, true, $adherent, self::PERMISSION, $event);
    }

    public function testVoteEventWithCommitteeMismatchDoesNotFallBackToZone(): void
    {
        // An Event is committee-bound: a committee mismatch denies access even when the scope has zones.
        $adherent = new Adherent();
        $event = new Event();
        $event->setCommittee($this->createCommitteeStub(self::COMMITTEE_UUID_OUT));

        $scopeZones = [$this->createStub(Zone::class)];
        $scope = $this->createScopeStub(committeeUuids: [self::COMMITTEE_UUID_IN], zones: $scopeZones);
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $this->assertGrantedForAdherent(false, true, $adherent, self::PERMISSION, $event);
    }

    public function testVoteEventWithAgoraMatchIsGranted(): void
    {
        $adherent = new Adherent();
        $event = new Event();
        $event->agora = $this->createAgoraStub(self::AGORA_UUID_IN);

        $scope = $this->createScopeStub(agoraUuids: [self::AGORA_UUID_IN]);
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $this->assertGrantedForAdherent(true, true, $adherent, self::PERMISSION, $event);
    }

    public function testVoteCommitteeMatchIsGranted(): void
    {
        $adherent = new Adherent();
        $committee = $this->createCommitteeStub(self::COMMITTEE_UUID_IN);

        $scope = $this->createScopeStub(committeeUuids: [self::COMMITTEE_UUID_IN]);
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $this->assertGrantedForAdherent(true, true, $adherent, self::PERMISSION, $committee);
    }

    public function testVoteCommitteeMismatchAndNoZoneIsDenied(): void
    {
        $adherent = new Adherent();
        $committee = $this->createCommitteeStub(self::COMMITTEE_UUID_OUT);

        $scope = $this->createScopeStub(committeeUuids: [self::COMMITTEE_UUID_IN]);
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $this->assertGrantedForAdherent(false, true, $adherent, self::PERMISSION, $committee);
    }

    public function testVoteCommitteeMismatchDoesNotFallBackToZone(): void
    {
        // A Committee is committee-bound: a mismatch denies access even when the scope has zones.
        $adherent = new Adherent();
        $committee = $this->createCommitteeStub(self::COMMITTEE_UUID_OUT);

        $scopeZones = [$this->createStub(Zone::class)];
        $scope = $this->createScopeStub(committeeUuids: [self::COMMITTEE_UUID_IN], zones: $scopeZones);
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $this->assertGrantedForAdherent(false, true, $adherent, self::PERMISSION, $committee);
    }

    public function testVoteAdherentSubjectWithCommitteeMembershipMatchIsGranted(): void
    {
        $adherent = new Adherent();
        $subject = new Adherent();
        $subject->setCommitteeMembership($this->createCommitteeMembershipStub(self::COMMITTEE_UUID_IN));

        $scope = $this->createScopeStub(committeeUuids: [self::COMMITTEE_UUID_IN]);
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $this->assertGrantedForAdherent(true, true, $adherent, self::PERMISSION, $subject);
    }

    public function testVoteAdherentSubjectWithCommitteeMismatchButZoneOkIsGranted(): void
    {
        $adherent = new Adherent();
        $zone = $this->createStub(Zone::class);
        $subject = new Adherent();
        $subject->setCommitteeMembership($this->createCommitteeMembershipStub(self::COMMITTEE_UUID_OUT));
        $subject->addZone($zone);

        $scopeZones = [$this->createStub(Zone::class)];
        $scope = $this->createScopeStub(committeeUuids: [self::COMMITTEE_UUID_IN], zones: $scopeZones);
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);

        $this->zoneRepository
            ->expects(self::once())
            ->method('isInZones')
            ->with([$zone], $scopeZones)
            ->willReturn(true)
        ;

        $this->assertGrantedForAdherent(true, true, $adherent, self::PERMISSION, $subject);
    }

    public function testVoteAdherentSubjectWithAgoraMembershipMatchAmongMultipleIsGranted(): void
    {
        $adherent = new Adherent();
        $subject = new Adherent();
        $subject->agoraMemberships = new ArrayCollection([
            $this->createAgoraMembership(self::AGORA_UUID_OUT),
            $this->createAgoraMembership(self::AGORA_UUID_IN),
            $this->createAgoraMembership('55555555-5555-5555-5555-555555555555'),
        ]);

        $scope = $this->createScopeStub(agoraUuids: [self::AGORA_UUID_IN]);
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $this->assertGrantedForAdherent(true, true, $adherent, self::PERMISSION, $subject);
    }

    public function testVoteAdherentSubjectWithoutMembershipsButZoneOkIsGranted(): void
    {
        $adherent = new Adherent();
        $zone = $this->createStub(Zone::class);
        $subject = new Adherent();
        $subject->addZone($zone);

        $scopeZones = [$this->createStub(Zone::class)];
        $scope = $this->createScopeStub(zones: $scopeZones);
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);

        $this->zoneRepository
            ->expects(self::once())
            ->method('isInZones')
            ->with([$zone], $scopeZones)
            ->willReturn(true)
        ;

        $this->assertGrantedForAdherent(true, true, $adherent, self::PERMISSION, $subject);
    }

    public function testVoteAdherentSubjectOutOfAnyZoneOrMembershipIsDenied(): void
    {
        $adherent = new Adherent();
        $subject = new Adherent();
        $subject->setCommitteeMembership($this->createCommitteeMembershipStub(self::COMMITTEE_UUID_OUT));

        $scope = $this->createScopeStub(committeeUuids: [self::COMMITTEE_UUID_IN]);
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $this->assertGrantedForAdherent(false, true, $adherent, self::PERMISSION, $subject);
    }

    public function testVoteManagedUserWithCommitteeMembershipMatchIsGranted(): void
    {
        // The single-adherent endpoint (/v3/adherents/{uuid}) votes on a ManagedUser projection, not an Adherent.
        // An animator scope has no zones, only committee UUIDs: access must be granted via committee membership.
        $adherent = new Adherent();
        $subject = $this->createManagedUserSubject(committeeUuids: [self::COMMITTEE_UUID_OUT, self::COMMITTEE_UUID_IN]);

        $scope = $this->createScopeStub(committeeUuids: [self::COMMITTEE_UUID_IN]);
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $this->assertGrantedForAdherent(true, true, $adherent, self::PERMISSION, $subject);
    }

    public function testVoteManagedUserWithCommitteeV2MatchIsGranted(): void
    {
        $adherent = new Adherent();
        $subject = $this->createManagedUserSubject(committeeUuid: self::COMMITTEE_UUID_IN);

        $scope = $this->createScopeStub(committeeUuids: [self::COMMITTEE_UUID_IN]);
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $this->assertGrantedForAdherent(true, true, $adherent, self::PERMISSION, $subject);
    }

    public function testVoteManagedUserWithAgoraMatchIsGranted(): void
    {
        $adherent = new Adherent();
        $subject = $this->createManagedUserSubject(agoraUuid: self::AGORA_UUID_IN);

        $scope = $this->createScopeStub(agoraUuids: [self::AGORA_UUID_IN]);
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $this->assertGrantedForAdherent(true, true, $adherent, self::PERMISSION, $subject);
    }

    public function testVoteManagedUserWithCommitteeMismatchButZoneOkIsGranted(): void
    {
        $adherent = new Adherent();
        $zone = $this->createStub(Zone::class);
        $subject = $this->createManagedUserSubject(committeeUuids: [self::COMMITTEE_UUID_OUT], zones: [$zone]);

        $scopeZones = [$this->createStub(Zone::class)];
        $scope = $this->createScopeStub(committeeUuids: [self::COMMITTEE_UUID_IN], zones: $scopeZones);
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);

        $this->zoneRepository
            ->expects(self::once())
            ->method('isInZones')
            ->with([$zone], $scopeZones)
            ->willReturn(true)
        ;

        $this->assertGrantedForAdherent(true, true, $adherent, self::PERMISSION, $subject);
    }

    public function testVoteManagedUserOutOfCommitteeAndZoneIsDenied(): void
    {
        $adherent = new Adherent();
        $subject = $this->createManagedUserSubject(committeeUuids: [self::COMMITTEE_UUID_OUT]);

        $scope = $this->createScopeStub(committeeUuids: [self::COMMITTEE_UUID_IN]);
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $this->assertGrantedForAdherent(false, true, $adherent, self::PERMISSION, $subject);
    }

    public function testVoteSubjectIsCurrentUserSkipsMembershipCheck(): void
    {
        // The subject is the current user (token's adherent). The voter must NOT check committee/agora memberships
        // (line 61 of the voter: `$subject instanceof Adherent && $subject !== $adherent`).
        // Since $subject === $adherent, the voter falls through to the geographic zone check.
        $adherent = new Adherent();
        $adherent->setCommitteeMembership($this->createCommitteeMembershipStub(self::COMMITTEE_UUID_IN));

        $scope = $this->createScopeStub(committeeUuids: [self::COMMITTEE_UUID_IN]);
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);
        // No scope zones => denied (no fallback to geographic).
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $this->assertGrantedForAdherent(false, true, $adherent, self::PERMISSION, $adherent);
    }

    public function testVoteWithNullScopeAndNoZonesIsDenied(): void
    {
        $adherent = new Adherent();
        $subject = $this->createAdherentSubject();

        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn(null);
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $this->assertGrantedForAdherent(false, true, $adherent, self::PERMISSION, $subject);
    }

    public function testVoteUsesScopeDelegatorAsReferenceForSelfCheck(): void
    {
        // When the scope has a delegator, the voter replaces $adherent with $scope->getDelegator().
        // If the subject equals the *original* adherent (not the delegator), $subject !== $adherent (now delegator)
        // is TRUE -> committee/agora membership check runs.
        $tokenAdherent = new Adherent();
        $delegator = new Adherent();
        $subject = $tokenAdherent; // same as token user, but different from the delegator

        $subject->setCommitteeMembership($this->createCommitteeMembershipStub(self::COMMITTEE_UUID_IN));

        $scope = $this->createScopeStub(committeeUuids: [self::COMMITTEE_UUID_IN], delegator: $delegator);
        $this->scopeGeneratorResolver->expects(self::once())->method('generate')->willReturn($scope);
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $this->assertGrantedForAdherent(true, true, $tokenAdherent, self::PERMISSION, $subject);
    }

    public function testSupportsRejectsAttributeOutsidePrefix(): void
    {
        $this->scopeGeneratorResolver->expects(self::never())->method('generate');
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $voter = $this->getVoter();
        $supports = $this->invokeProtectedSupports($voter, 'OTHER_PERMISSION', $this->createAdherentSubject());

        self::assertFalse($supports);
    }

    public function testSupportsRejectsSubjectNotZoneable(): void
    {
        $this->scopeGeneratorResolver->expects(self::never())->method('generate');
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $voter = $this->getVoter();
        $supports = $this->invokeProtectedSupports($voter, self::PERMISSION, new \stdClass());

        self::assertFalse($supports);
    }

    public function testSupportsAcceptsAttributeWithCustomVariantSuffix(): void
    {
        $this->scopeGeneratorResolver->expects(self::never())->method('generate');
        $this->zoneRepository->expects(self::never())->method('isInZones');

        $voter = $this->getVoter();
        $supports = $this->invokeProtectedSupports($voter, 'MANAGE_ZONEABLE_ITEM__CUSTOM_VARIANT', $this->createAdherentSubject());

        self::assertTrue($supports);
    }

    /**
     * Configure a Scope stub with the requested attributes. All getters return safe defaults if not specified.
     */
    private function createScopeStub(
        bool $isNational = false,
        ?string $instanceKey = null,
        array $committeeUuids = [],
        array $agoraUuids = [],
        array $zones = [],
        ?Adherent $delegator = null,
    ): Scope&Stub {
        $scope = $this->createStub(Scope::class);
        $scope->method('isNational')->willReturn($isNational);
        $scope->method('getInstanceKey')->willReturn($instanceKey ?? 'scope:none');
        $scope->method('getCommitteeUuids')->willReturn($committeeUuids);
        $scope->method('getAgoraUuids')->willReturn($agoraUuids);
        $scope->method('getZones')->willReturn($zones);
        $scope->method('getDelegator')->willReturn($delegator);

        return $scope;
    }

    private function createAdherentSubject(): Adherent
    {
        return new Adherent();
    }

    private function createManagedUserSubject(
        array $committeeUuids = [],
        ?string $committeeUuid = null,
        ?string $agoraUuid = null,
        array $zones = [],
    ): ManagedUser&Stub {
        $managedUser = $this->createStub(ManagedUser::class);
        $managedUser->method('getCommitteeUuids')->willReturn($committeeUuids);
        $managedUser->method('getCommitteeUuid')->willReturn($committeeUuid ? Uuid::fromString($committeeUuid) : null);
        $managedUser->method('getAgoraUuid')->willReturn($agoraUuid ? Uuid::fromString($agoraUuid) : null);
        $managedUser->method('getZones')->willReturn(new ZoneCollection($zones));

        return $managedUser;
    }

    private function createAuthorInstanceSubject(string $instanceKey): AuthorInstanceInterface&ZoneableEntityInterface&Stub
    {
        $stub = $this->createStubForIntersectionOfInterfaces([
            AuthorInstanceInterface::class,
            ZoneableEntityInterface::class,
        ]);
        $stub->method('getInstanceKey')->willReturn($instanceKey);
        $stub->method('getZones')->willReturn(new ArrayCollection());

        return $stub;
    }

    private function createCommitteeStub(string $uuid): Committee&Stub
    {
        $committee = $this->createStub(Committee::class);
        $committee->method('getUuid')->willReturn(Uuid::fromString($uuid));
        $committee->method('getUuidAsString')->willReturn($uuid);

        return $committee;
    }

    private function createAgoraStub(string $uuid): Agora&Stub
    {
        $agora = $this->createStub(Agora::class);
        $agora->method('getUuid')->willReturn(Uuid::fromString($uuid));

        return $agora;
    }

    private function createCommitteeMembershipStub(string $committeeUuid): CommitteeMembership&Stub
    {
        $membership = $this->createStub(CommitteeMembership::class);
        $membership->method('getCommittee')->willReturn($this->createCommitteeStub($committeeUuid));

        return $membership;
    }

    private function createAgoraMembership(string $agoraUuid): AgoraMembership
    {
        $membership = new AgoraMembership();
        $membership->agora = $this->createAgoraStub($agoraUuid);

        return $membership;
    }

    private function invokeProtectedSupports(AbstractAdherentVoter $voter, string $attribute, mixed $subject): bool
    {
        $reflection = new \ReflectionMethod($voter, 'supports');

        return (bool) $reflection->invoke($voter, $attribute, $subject);
    }
}
