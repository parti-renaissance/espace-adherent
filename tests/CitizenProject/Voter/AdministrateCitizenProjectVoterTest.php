<?php

namespace Tests\AppBundle\CitizenProject\Voter;

use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\CitizenProject\CitizenProjectPermissions;
use AppBundle\CitizenProject\Voter\AdministrateCitizenProjectVoter;
use AppBundle\Entity\CitizenProjectCategory;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AdministrateCitizenProjectVoterTest extends AbstractCitizenProjectVoterTest
{
    /* @var AdministrateCitizenProjectVoter */
    private $voter;
    private $manager;

    public function testAdherentCanEditHisUnapprovedCitizenProject()
    {
        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);
        $citizenProjectCategory = new CitizenProjectCategory(self::CATEGORY_1);
        $citizenProject = $this->createCitizenProject(self::ADHERENT_1_UUID, $citizenProjectCategory);
        $token = $this->createAuthenticatedToken($adherent);

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $citizenProject, [CitizenProjectPermissions::ADMINISTRATE])
        );
    }

    public function testAnonymousCannotEditCitizenProject()
    {
        $citizenProjectCategory = new CitizenProjectCategory(self::CATEGORY_1);
        $citizenProject = $this->createCitizenProject(self::ADHERENT_2_UUID, $citizenProjectCategory);
        $token = $this->createAnonymousToken();

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $citizenProject, [CitizenProjectPermissions::ADMINISTRATE])
        );
    }

    public function testAdministrateGroupAdherentCanEditApprovedCitizenProject()
    {
        $citizenProjectCategory = new CitizenProjectCategory(self::CATEGORY_2);
        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_2_UUID);
        $citizenProject = $this->createCitizenProject(self::ADHERENT_1_UUID, $citizenProjectCategory);
        $citizenProject->approved();

        $this
            ->manager
            ->expects($this->once())
            ->method('administrateCitizenProject')
            ->with($adherent)
            ->willReturn(true);

        $token = $this->createAuthenticatedToken($adherent);
        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $citizenProject, [CitizenProjectPermissions::ADMINISTRATE])
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->manager = $this->createMock(CitizenProjectManager::class);
        $this->voter = new AdministrateCitizenProjectVoter($this->manager);
    }

    protected function tearDown()
    {
        $this->manager = null;
        $this->voter = null;

        parent::tearDown();
    }
}
