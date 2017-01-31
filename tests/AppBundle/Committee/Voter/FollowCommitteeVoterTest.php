<?php

namespace Tests\AppBundle\Committee\Voter;

use AppBundle\Committee\CommitteePermissions;
use AppBundle\Committee\Voter\FollowCommitteeVoter;
use AppBundle\Repository\CommitteeMembershipRepository;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\User;

class FollowCommitteeVoterTest extends AbstractCommitteeVoterTest
{
    /* @var FollowCommitteeVoter */
    private $voter;
    private $repository;

    public function testCommitteeHostAdherentIsAllowedToUnfollowCommittee()
    {
        $committee = $this->createCommittee(self::ADHERENT_2_UUID);
        $committee->approved();

        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);
        $membership = $adherent->hostCommittee($committee);

        $token = $this->createAuthenticatedToken($adherent);

        $this
            ->repository
            ->expects($this->once())
            ->method('findMembership')
            ->with($adherent, (string) $committee->getUuid())
            ->willReturn($membership);

        $this
            ->repository
            ->expects($this->once())
            ->method('countHostMembers')
            ->with((string) $committee->getUuid())
            ->willReturn(2);

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $committee, [CommitteePermissions::UNFOLLOW])
        );
    }

    public function testCommitteeHostAdherentIsNotAllowedToUnfollowCommittee()
    {
        $committee = $this->createCommittee(self::ADHERENT_2_UUID);
        $committee->approved();

        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_2_UUID);
        $membership = $adherent->hostCommittee($committee);

        $token = $this->createAuthenticatedToken($adherent);

        $this
            ->repository
            ->expects($this->once())
            ->method('findMembership')
            ->with($adherent, (string) $committee->getUuid())
            ->willReturn($membership);

        $this
            ->repository
            ->expects($this->once())
            ->method('countHostMembers')
            ->with((string) $committee->getUuid())
            ->willReturn(1);

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $committee, [CommitteePermissions::UNFOLLOW])
        );
    }

    public function testFollowerAdherentCanUnfollowTheCommittee()
    {
        $committee = $this->createCommittee(self::ADHERENT_2_UUID);
        $committee->approved();

        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);
        $membership = $adherent->followCommittee($committee);

        $token = $this->createAuthenticatedToken($adherent);

        $this
            ->repository
            ->expects($this->once())
            ->method('findMembership')
            ->with($adherent, (string) $committee->getUuid())
            ->willReturn($membership);

        $this
            ->repository
            ->expects($this->never())
            ->method('countHostMembers');

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $committee, [CommitteePermissions::UNFOLLOW])
        );
    }

    public function testAdherentIsNotAllowedToUnfollowCommitteeHeDoesNotAlreadyFollow()
    {
        $committee = $this->createCommittee(self::ADHERENT_2_UUID);
        $committee->approved();

        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);
        $token = $this->createAuthenticatedToken($adherent);

        $this
            ->repository
            ->expects($this->once())
            ->method('findMembership')
            ->with($adherent, (string) $committee->getUuid())
            ->willReturn(false);

        $this
            ->repository
            ->expects($this->never())
            ->method('countHostMembers');

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $committee, [CommitteePermissions::UNFOLLOW])
        );
    }

    public function testAdherentIsNotAllowedToUnfollowAnUnapprovedCommittee()
    {
        $this->repository->expects($this->never())->method('findMembership');
        $this->repository->expects($this->never())->method('countHostMembers');

        $committee = $this->createCommittee(self::ADHERENT_2_UUID);
        $token = $this->createAuthenticatedToken($this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID));

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $committee, [CommitteePermissions::UNFOLLOW])
        );
    }

    public function testAdherentIsNotAllowedToFollowAnUnapprovedCommittee()
    {
        $this->repository->expects($this->never())->method('isMemberOf');

        $committee = $this->createCommittee(self::ADHERENT_2_UUID);
        $token = $this->createAuthenticatedToken($this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID));

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $committee, [CommitteePermissions::FOLLOW])
        );
    }

    public function testAdherentIsNotAllowedToFollowSameApprovedCommitteeTwice()
    {
        $committee = $this->createCommittee(self::ADHERENT_2_UUID);
        $committee->approved();

        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);

        $this
            ->repository
            ->expects($this->once())
            ->method('isMemberOf')
            ->with($adherent, (string) $committee->getUuid())
            ->willReturn(true);

        $token = $this->createAuthenticatedToken($adherent);

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $committee, [CommitteePermissions::FOLLOW])
        );
    }

    public function testRegularAdherentIsAllowedToFollowTheCommittee()
    {
        $committee = $this->createCommittee(self::ADHERENT_2_UUID);
        $committee->approved();

        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);

        $this
            ->repository
            ->expects($this->once())
            ->method('isMemberOf')
            ->with($adherent, (string) $committee->getUuid())
            ->willReturn(false);

        $token = $this->createAuthenticatedToken($adherent);

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $committee, [CommitteePermissions::FOLLOW])
        );
    }

    /**
     * @dataProvider provideSupportedAttribute
     */
    public function testUnsupportedAdherentType(string $attribute)
    {
        $committee = $this->createCommittee(self::ADHERENT_2_UUID);
        $token = $this->createAuthenticatedToken(new User('foobar', 'password', ['ROLE_ADHERENT']));

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $committee, [$attribute])
        );
    }

    /**
     * @dataProvider provideSupportedAttribute
     */
    public function testUnsupportedCommitteeType(string $attribute)
    {
        $token = $this->createAuthenticatedToken($this->createAdherentFromUuidAndEmail(self::ADHERENT_2_UUID));

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote($token, new \stdClass(), [$attribute])
        );
    }

    public function provideSupportedAttribute()
    {
        return [
            [CommitteePermissions::FOLLOW],
            [CommitteePermissions::UNFOLLOW],
        ];
    }

    public function testUnsupportedAttribute()
    {
        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);
        $committee = $this->createCommittee(self::ADHERENT_1_UUID);
        $token = $this->createAuthenticatedToken($adherent);

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote($token, $committee, [CommitteePermissions::SHOW])
        );
    }

    public function testAdherentIsDeniedToFollowUnapprovedCommittee()
    {
        $committee = $this->createCommittee(self::ADHERENT_1_UUID);
        $token = $this->createAuthenticatedToken($this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID));

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $committee, [CommitteePermissions::FOLLOW])
        );
    }

    public function testAdherentIsDeniedToFollowCommitteeThatHeAlreadyFollows()
    {
        $committee = $this->createCommittee(self::ADHERENT_1_UUID);
        $committee->approved();

        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);
        $token = $this->createAuthenticatedToken($adherent);

        $this
            ->repository
            ->expects($this->once())
            ->method('isMemberOf')
            ->with($adherent, $committee->getUuid()->toString())
            ->willReturn(true)
        ;

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $committee, [CommitteePermissions::FOLLOW])
        );
    }

    public function testAdherentIsGrantedToFollowNewCommittee()
    {
        $committee = $this->createCommittee(self::ADHERENT_1_UUID);
        $committee->approved();

        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_2_UUID);
        $token = $this->createAuthenticatedToken($adherent);

        $this
            ->repository
            ->expects($this->once())
            ->method('isMemberOf')
            ->with($adherent, $committee->getUuid()->toString())
            ->willReturn(false)
        ;

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $committee, [CommitteePermissions::FOLLOW])
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->repository = $this->getMockBuilder(CommitteeMembershipRepository::class)->disableOriginalConstructor()->getMock();
        $this->voter = new FollowCommitteeVoter($this->repository);
    }

    protected function tearDown()
    {
        $this->voter = null;
        $this->repository = null;

        parent::tearDown();
    }
}
