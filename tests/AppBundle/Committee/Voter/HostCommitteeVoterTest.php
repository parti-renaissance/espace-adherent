<?php

namespace Tests\AppBundle\Committee\Voter;

use AppBundle\Committee\Voter\HostCommitteeVoter;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Repository\CommitteeMembershipRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class HostCommitteeVoterTest extends \PHPUnit_Framework_TestCase
{
    const COMMITTEE_UUID = '515a56c0-bde8-56ef-b90c-4745b1c93818';

    private $token;
    private $adherent;
    private $committee;
    private $committeeMembershipRepository;

    /* @var HostCommitteeVoter */
    private $voter;

    public function testHostCommitteePermissionIsGranted()
    {
        $this
            ->committeeMembershipRepository
            ->expects($this->once())
            ->method('hostCommittee')
            ->with($this->adherent, self::COMMITTEE_UUID)
            ->willReturn(true);

        $this
            ->committee
            ->expects($this->once())
            ->method('getUuid')
            ->willReturn(Uuid::fromString(self::COMMITTEE_UUID));

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($this->token, $this->committee, ['HOST_COMMITTEE']));
    }

    public function testHostCommitteePermissionIsDenied()
    {
        $this
            ->committeeMembershipRepository
            ->expects($this->once())
            ->method('hostCommittee')
            ->with($this->adherent, self::COMMITTEE_UUID)
            ->willReturn(false);

        $this
            ->committee
            ->expects($this->once())
            ->method('getUuid')
            ->willReturn(Uuid::fromString(self::COMMITTEE_UUID));

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($this->token, $this->committee, ['HOST_COMMITTEE']));
    }

    public function testCreateCommitteePermissionWithUnsupportedAttributeIsAbstain()
    {
        $this->committeeMembershipRepository->expects($this->never())->method('hostCommittee');
        $this->committee->expects($this->never())->method('getUuid');

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->voter->vote($this->token, $this->committee, ['CREATE_FOOBAR']));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->committeeMembershipRepository = $this
            ->getMockBuilder(CommitteeMembershipRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->adherent = $this
            ->getMockBuilder(Adherent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->committee = $this
            ->getMockBuilder(Committee::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->token = new UsernamePasswordToken($this->adherent, 'password', 'users_db');

        $this->voter = new HostCommitteeVoter($this->committeeMembershipRepository);
    }

    protected function tearDown()
    {
        $this->token = null;
        $this->adherent = null;
        $this->committee = null;
        $this->committeeMembershipRepository = null;
        $this->voter = null;

        parent::tearDown();
    }
}
