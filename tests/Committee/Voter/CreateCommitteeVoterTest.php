<?php

namespace Tests\AppBundle\Committee\Voter;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Committee\Voter\CreateCommitteeVoter;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;

class CreateCommitteeVoterTest extends AbstractCommitteeVoterTest
{
    const ADHERENT_UUID = '9ae87712-1bc0-4887-a00d-5c13780e5071';

    private $adherent;
    private $referent;
    private $committeeManager;

    /* @var CreateCommitteeVoter */
    private $voter;

    public function testCreateCommitteePermissionIsGranted()
    {
        $this
            ->committeeManager
            ->expects($this->once())
            ->method('isCommitteeHost')
            ->with($this->adherent)
            ->willReturn(false);

        $token = $this->createAuthenticationToken($this->adherent);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($token, null, ['CREATE_COMMITTEE']));
    }

    public function testCreateCommitteePermissionIsDeniedWhenAdherentIsAlreadyHost()
    {
        $this
            ->committeeManager
            ->expects($this->once())
            ->method('isCommitteeHost')
            ->with($this->adherent)
            ->willReturn(true);

        $token = $this->createAuthenticationToken($this->adherent);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($token, null, ['CREATE_COMMITTEE']));
    }

    public function testCreateCommitteePermissionIsDeniedWhenAdherentIsAlsoReferent()
    {
        $this->committeeManager->expects($this->never())->method('isCommitteeHost');

        $token = $this->createAuthenticationToken($this->referent);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($token, null, ['CREATE_COMMITTEE']));
    }

    public function testCreateCommitteePermissionWithUnsupportedAttributeIsAbstain()
    {
        $this->committeeManager->expects($this->never())->method('isCommitteeHost');

        $token = $this->createAuthenticationToken($this->adherent);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->voter->vote($token, null, ['CREATE_FOOBAR']));
    }

    public function testCreateCommitteePermissionWithUnsupportedAdherentIsAbstain()
    {
        $this->committeeManager->expects($this->never())->method('isCommitteeHost');

        $token = $this->createAuthenticationToken(new User('foobar', 'barfoo'));

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->voter->vote($token, null, ['CREATE_COMMITTEE']));
    }

    public function testCreateCommitteePermissionWithExplicitSubjectIsAbstain()
    {
        $this->committeeManager->expects($this->never())->method('isCommitteeHost');

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
        $this->committeeManager = $this->createMock(CommitteeManager::class);

        $this->voter = new CreateCommitteeVoter($this->committeeManager);
    }

    protected function tearDown()
    {
        $this->adherent = null;
        $this->referent = null;
        $this->committeeManager = null;
        $this->voter = null;

        parent::tearDown();
    }
}
