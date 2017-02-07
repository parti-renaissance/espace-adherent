<?php

namespace Tests\AppBundle\Committee\Voter;

use AppBundle\Committee\Voter\CreateCommitteeVoter;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\CommitteeRepository;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;

class CreateCommitteeVoterTest extends AbstractCommitteeVoterTest
{
    const ADHERENT_UUID = '9ae87712-1bc0-4887-a00d-5c13780e5071';

    private $adherent;
    private $referent;
    private $committeeRepository;
    private $committeeMembershipRepository;

    /* @var CreateCommitteeVoter */
    private $voter;

    public function testCreateCommitteePermissionIsGranted()
    {
        $this
            ->committeeMembershipRepository
            ->expects($this->once())
            ->method('hostCommittee')
            ->with($this->adherent)
            ->willReturn(false);

        $this
            ->committeeRepository
            ->expects($this->once())
            ->method('hasWaitingForApprovalCommittees')
            ->with($this->adherent->getUuid()->toString())
            ->willReturn(false);

        $token = $this->createAuthenticationToken($this->adherent);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($token, null, ['CREATE_COMMITTEE']));
    }

    public function testCreateCommitteePermissionIsDeniedWhenHost()
    {
        $this
            ->committeeMembershipRepository
            ->expects($this->once())
            ->method('hostCommittee')
            ->with($this->adherent)
            ->willReturn(true);

        $this
            ->committeeRepository
            ->expects($this->never())
            ->method('hasWaitingForApprovalCommittees');

        $token = $this->createAuthenticationToken($this->adherent);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($token, null, ['CREATE_COMMITTEE']));
    }

    public function testCreateCommitteePermissionIsDeniedWhenHostWaitingForApproval()
    {
        $this
            ->committeeMembershipRepository
            ->expects($this->once())
            ->method('hostCommittee')
            ->with($this->adherent)
            ->willReturn(false);

        $this
            ->committeeRepository
            ->expects($this->once())
            ->method('hasWaitingForApprovalCommittees')
            ->with($this->adherent->getUuid()->toString())
            ->willReturn(true);

        $token = $this->createAuthenticationToken($this->adherent);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($token, null, ['CREATE_COMMITTEE']));
    }

    public function testCreateCommitteePermissionIsDeniedWhenReferent()
    {
        $this
            ->committeeMembershipRepository
            ->expects($this->never())
            ->method('hostCommittee');

        $this
            ->committeeRepository
            ->expects($this->never())
            ->method('hasWaitingForApprovalCommittees');

        $token = $this->createAuthenticationToken($this->referent);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($token, null, ['CREATE_COMMITTEE']));
    }

    public function testCreateCommitteePermissionWithUnsupportedAttributeIsAbstain()
    {
        $this->committeeMembershipRepository->expects($this->never())->method('hostCommittee');
        $this->committeeRepository->expects($this->never())->method('hasWaitingForApprovalCommittees');

        $token = $this->createAuthenticationToken($this->adherent);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->voter->vote($token, null, ['CREATE_FOOBAR']));
    }

    public function testCreateCommitteePermissionWithUnsupportedAdherentIsAbstain()
    {
        $this->committeeMembershipRepository->expects($this->never())->method('hostCommittee');
        $this->committeeRepository->expects($this->never())->method('hasWaitingForApprovalCommittees');

        $token = $this->createAuthenticationToken(new User('foobar', 'barfoo'));

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->voter->vote($token, null, ['CREATE_COMMITTEE']));
    }

    public function testCreateCommitteePermissionWithExplicitSubjectIsAbstain()
    {
        $this->committeeMembershipRepository->expects($this->never())->method('hostCommittee');
        $this->committeeRepository->expects($this->never())->method('hasWaitingForApprovalCommittees');

        $token = $this->createAuthenticationToken(new User('foobar', 'barfoo'));

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->voter->vote($token, new \stdClass(), ['CREATE_COMMITTEE']));
    }

    private function createAuthenticationToken(UserInterface $user)
    {
        return new UsernamePasswordToken($user, 'password', 'users_db');
    }

    protected function setUp()
    {
        parent::setUp();

        $this->adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_UUID);
        $this->referent = $this->createReferentFromUuidAndEmail(self::ADHERENT_UUID);

        $this->committeeMembershipRepository = $this
            ->getMockBuilder(CommitteeMembershipRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->committeeRepository = $this
            ->getMockBuilder(CommitteeRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->voter = new CreateCommitteeVoter(
            $this->committeeMembershipRepository,
            $this->committeeRepository
        );
    }

    protected function tearDown()
    {
        $this->adherent = null;
        $this->referent = null;
        $this->committeeRepository = null;
        $this->committeeMembershipRepository = null;
        $this->voter = null;

        parent::tearDown();
    }
}
