<?php

namespace Tests\AppBundle\Committee\Voter;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Committee\Voter\HostCommitteeVoter;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class HostCommitteeVoterTest extends \PHPUnit_Framework_TestCase
{
    const COMMITTEE_UUID = '515a56c0-bde8-56ef-b90c-4745b1c93818';

    private $token;
    private $adherent;
    private $committee;
    private $committeeManager;

    /* @var HostCommitteeVoter */
    private $voter;

    public function testHostCommitteePermissionIsGranted()
    {
        $this
            ->committeeManager
            ->expects($this->once())
            ->method('hostCommittee')
            ->with($this->adherent, $this->committee)
            ->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($this->token, $this->committee, ['HOST_COMMITTEE']));
    }

    public function testHostCommitteePermissionIsDenied()
    {
        $this
            ->committeeManager
            ->expects($this->once())
            ->method('hostCommittee')
            ->with($this->adherent, $this->committee)
            ->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($this->token, $this->committee, ['HOST_COMMITTEE']));
    }

    public function testCreateCommitteePermissionWithUnsupportedAttributeIsAbstain()
    {
        $this->committeeManager->expects($this->never())->method('hostCommittee');

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->voter->vote($this->token, $this->committee, ['CREATE_FOOBAR']));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->committeeManager = $this->createMock(CommitteeManager::class);
        $this->adherent = $this->createMock(Adherent::class);
        $this->committee = $this->createMock(Committee::class);
        $this->token = new UsernamePasswordToken($this->adherent, 'password', 'users_db');
        $this->voter = new HostCommitteeVoter($this->committeeManager);
    }

    protected function tearDown()
    {
        $this->token = null;
        $this->adherent = null;
        $this->committee = null;
        $this->committeeManager = null;
        $this->voter = null;

        parent::tearDown();
    }
}
