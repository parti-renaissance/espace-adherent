<?php

namespace Tests\AppBundle\Committee\Voter;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Committee\CommitteePermissions;
use AppBundle\Committee\Voter\FollowCommitteeVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\User;

class FollowCommitteeVoterTest extends AbstractCommitteeVoterTest
{
    /* @var FollowCommitteeVoter */
    private $voter;
    private $manager;

    public function testCommitteeHostAdherentIsNotAllowedToUnfollowCommittee()
    {
        $committee = $this->createCommittee(self::ADHERENT_2_UUID);
        $committee->approved();

        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);
        $membership = $adherent->hostCommittee($committee);

        $token = $this->createAuthenticatedToken($adherent);

        $this
            ->manager
            ->expects($this->once())
            ->method('getCommitteeMembership')
            ->with($adherent, $committee)
            ->willReturn($membership);

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $committee, [CommitteePermissions::UNFOLLOW])
        );
    }

    public function testFollowerAdherentCanUnfollowCommittee()
    {
        $committee = $this->createCommittee(self::ADHERENT_2_UUID);
        $committee->approved();

        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);
        $membership = $adherent->followCommittee($committee);

        $token = $this->createAuthenticatedToken($adherent);

        $this
            ->manager
            ->expects($this->once())
            ->method('getCommitteeMembership')
            ->with($adherent, $committee)
            ->willReturn($membership);

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
            ->manager
            ->expects($this->once())
            ->method('getCommitteeMembership')
            ->with($adherent, $committee)
            ->willReturn(null);

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $committee, [CommitteePermissions::UNFOLLOW])
        );
    }

    public function testAdherentIsNotAllowedToUnfollowAnUnapprovedCommittee()
    {
        $this->manager->expects($this->never())->method('getCommitteeMembership');

        $committee = $this->createCommittee(self::ADHERENT_2_UUID);
        $token = $this->createAuthenticatedToken($this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID));

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $committee, [CommitteePermissions::UNFOLLOW])
        );
    }

    public function testAdherentIsNotAllowedToFollowAnUnapprovedCommittee()
    {
        $this->manager->expects($this->never())->method('isFollowingCommittee');

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
            ->manager
            ->expects($this->once())
            ->method('isFollowingCommittee')
            ->with($adherent, $committee)
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
            ->manager
            ->expects($this->once())
            ->method('isFollowingCommittee')
            ->with($adherent, $committee)
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
        $token = $this->createAuthenticatedToken(new User('foobar', 'password', ['ROLE_USER', 'ROLE_ADHERENT']));

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

    public function testAdherentIsDeniedToFollowCommitteeTwice()
    {
        $committee = $this->createCommittee(self::ADHERENT_1_UUID);
        $committee->approved();

        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);
        $token = $this->createAuthenticatedToken($adherent);

        $this
            ->manager
            ->expects($this->once())
            ->method('isFollowingCommittee')
            ->with($adherent, $committee)
            ->willReturn(true);

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
            ->manager
            ->expects($this->once())
            ->method('isFollowingCommittee')
            ->with($adherent, $committee)
            ->willReturn(false);

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $committee, [CommitteePermissions::FOLLOW])
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->manager = $this->createMock(CommitteeManager::class);
        $this->voter = new FollowCommitteeVoter($this->manager);
    }

    protected function tearDown()
    {
        $this->voter = null;
        $this->manager = null;

        parent::tearDown();
    }
}
