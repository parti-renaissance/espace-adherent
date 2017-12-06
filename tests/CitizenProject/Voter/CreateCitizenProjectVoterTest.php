<?php

namespace Tests\AppBundle\CitizenProject\Voter;

use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\CitizenProject\Voter\CreateCitizenProjectVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CreateCitizenProjectVoterTest extends AbstractCitizenProjectVoterTest
{
    /** @var CreateCitizenProjectVoter */
    private $voter;
    private $manager;

    public function testCreateCitizenProjectPermissionIsGranted()
    {
        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);
        $token = $this->createAuthenticatedToken($adherent);

        $this->manager
            ->expects($this->once())
            ->method('isCitizenProjectAdministrator')
            ->with($adherent)
            ->willReturn(false);

        $this->manager
            ->expects($this->once())
            ->method('hasCitizenProjectInStatus')
            ->with($adherent)
            ->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($token, null, ['CREATE_CITIZEN_PROJECT']));
    }

    public function testCreateCitizenProjectPermissionIsDenied()
    {
        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);
        $token = $this->createAuthenticatedToken($adherent);

        $this->manager
            ->expects($this->once())
            ->method('hasCitizenProjectInStatus')
            ->with($adherent)
            ->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($token, null, ['CREATE_CITIZEN_PROJECT']));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->manager = $this->createMock(CitizenProjectManager::class);
        $this->voter = new CreateCitizenProjectVoter($this->manager);
    }

    protected function tearDown()
    {
        $this->manager = null;
        $this->voter = null;

        parent::tearDown();
    }
}
