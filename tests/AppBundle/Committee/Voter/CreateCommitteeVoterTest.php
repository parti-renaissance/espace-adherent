<?php

namespace Tests\AppBundle\Committee\Voter;

use AppBundle\Committee\Voter\CreateCommitteeVoter;
use AppBundle\Entity\Adherent;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\CommitteeRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;

class CreateCommitteeVoterTest extends \PHPUnit_Framework_TestCase
{
    const ADHERENT_UUID = '9ae87712-1bc0-4887-a00d-5c13780e5071';

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
            ->with(self::ADHERENT_UUID)
            ->willReturn(false);

        $this
            ->committeeRepository
            ->expects($this->once())
            ->method('hasWaitingForApprovalCommittees')
            ->with(self::ADHERENT_UUID)
            ->willReturn(false);

        $adherent = $this
            ->getMockBuilder(Adherent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $adherent
            ->expects($this->once())
            ->method('getUuid')
            ->willReturn(Uuid::fromString(self::ADHERENT_UUID));

        $token = $this->createAuthenticationToken($adherent);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($token, null, ['CREATE_COMMITTEE']));
    }

    public function testCreateCommitteePermissionIsDenied()
    {
        $this
            ->committeeMembershipRepository
            ->expects($this->once())
            ->method('hostCommittee')
            ->with(self::ADHERENT_UUID)
            ->willReturn(true);

        $this
            ->committeeRepository
            ->expects($this->never())
            ->method('hasWaitingForApprovalCommittees');

        $adherent = $this
            ->getMockBuilder(Adherent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $adherent
            ->expects($this->once())
            ->method('getUuid')
            ->willReturn(Uuid::fromString(self::ADHERENT_UUID));

        $token = $this->createAuthenticationToken($adherent);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($token, null, ['CREATE_COMMITTEE']));
    }

    public function testCreateCommitteePermissionWithUnsupportedAttributeIsAbstain()
    {
        $this->committeeMembershipRepository->expects($this->never())->method('hostCommittee');
        $this->committeeRepository->expects($this->never())->method('hasWaitingForApprovalCommittees');

        $adherent = $this
            ->getMockBuilder(Adherent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $token = $this->createAuthenticationToken($adherent);

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
        $this->committeeRepository = null;
        $this->committeeMembershipRepository = null;
        $this->voter = null;

        parent::tearDown();
    }
}
