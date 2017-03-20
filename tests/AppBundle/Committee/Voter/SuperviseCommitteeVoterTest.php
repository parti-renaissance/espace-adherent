<?php

namespace Tests\AppBundle\Committee\Voter;

use AppBundle\Committee\CommitteePermissions;
use AppBundle\Committee\Voter\SuperviseCommitteeVoter;
use AppBundle\Repository\CommitteeMembershipRepository;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\User;

class SuperviseCommitteeVoterTest extends AbstractCommitteeVoterTest
{
    /* @var SuperviseCommitteeVoter */
    private $voter;

    private $repository;

    public function testUnsupportedAdherentType()
    {
        $committee = $this->createCommittee(self::ADHERENT_2_UUID);
        $token = $this->createAuthenticatedToken(new User('foobar', 'password', ['ROLE_ADHERENT']));

        $this->repository->expects($this->never())->method('superviseCommittee');

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $committee, [CommitteePermissions::SUPERVISE])
        );
    }

    public function testUnsupportedCommitteeType()
    {
        $token = $this->createAuthenticatedToken($this->createAdherentFromUuidAndEmail(self::ADHERENT_2_UUID));

        $this->repository->expects($this->never())->method('superviseCommittee');

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote($token, new \stdClass(), [CommitteePermissions::SUPERVISE])
        );
    }

    public function testUnsupportedAttribute()
    {
        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);
        $committee = $this->createCommittee(self::ADHERENT_1_UUID);
        $token = $this->createAuthenticatedToken($adherent);

        $this->repository->expects($this->never())->method('superviseCommittee');

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote($token, $committee, [CommitteePermissions::CREATE])
        );
    }

    public function testAnonymousIsNotGrantedToSuperviseAnApprovedCommittee()
    {
        $committee = $this->createCommittee(self::ADHERENT_1_UUID);
        $committee->approved();

        $token = $this->createAnonymousToken();

        $this->repository->expects($this->never())->method('superviseCommittee');

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $committee, [CommitteePermissions::SUPERVISE])
        );
    }

    public function testAdherentIsNotGrantedToSuperviseCommittee()
    {
        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);

        $committee = $this->createCommittee(self::ADHERENT_2_UUID);
        $committee->approved();

        $token = $this->createAuthenticatedToken($adherent);

        $this
            ->repository
            ->expects($this->once())
            ->method('superviseCommittee')
            ->with($adherent, (string) $committee->getUuid())
            ->willReturn(false);

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $committee, [CommitteePermissions::SUPERVISE])
        );
    }

    public function testCommitteeOwnerIsGrantedToSuperviseCommittee()
    {
        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);
        $committee = $this->createCommittee($adherent->getUuid());
        $token = $this->createAuthenticatedToken($adherent);

        $this
            ->repository
            ->expects($this->once())
            ->method('superviseCommittee')
            ->with($adherent, (string) $committee->getUuid())
            ->willReturn(true);

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $committee, [CommitteePermissions::SUPERVISE])
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->repository = $this->createMock(CommitteeMembershipRepository::class);
        $this->voter = new SuperviseCommitteeVoter($this->repository);
    }

    protected function tearDown()
    {
        $this->repository = null;
        $this->voter = null;

        parent::tearDown();
    }
}
