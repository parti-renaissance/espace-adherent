<?php

namespace Tests\AppBundle\Security\Voter\CitizenProject;

use AppBundle\CitizenProject\CitizenProjectPermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Repository\CitizenProjectRepository;
use AppBundle\Security\Voter\AbstractAdherentVoter;
use AppBundle\Security\Voter\CitizenProject\CreateCitizenProjectVoter;
use Tests\AppBundle\Security\Voter\AbstractAdherentVoterTest;

class CreateCitizenProjectVoterTest extends AbstractAdherentVoterTest
{
    /**
     * @var CitizenProjectRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $projectRepository;

    protected function setUp(): void
    {
        $this->projectRepository = $this->createMock(CitizenProjectRepository::class);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->projectRepository = null;

        parent::tearDown();
    }

    public function provideAnonymousCases(): iterable
    {
        $this->projectRepository = $this->createMock(CitizenProjectRepository::class);
        $this->assertRepositoryBehavior(null);

        yield [false, true, CitizenProjectPermissions::CREATE];
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new CreateCitizenProjectVoter($this->projectRepository);
    }

    public function testAdherentIsNotGrantedWhenReferent()
    {
        $adherent = $this->getAdherentMock(true);

        $this->assertRepositoryBehavior(null);
        $this->assertGrantedForAdherent(false, true, $adherent, CitizenProjectPermissions::CREATE);
    }

    public function testAdherentIsNotGrantedWhenAlreadyProjectAdministrator()
    {
        $adherent = $this->getAdherentMock(false, true);

        $this->assertRepositoryBehavior(null);
        $this->assertGrantedForAdherent(false, true, $adherent, CitizenProjectPermissions::CREATE);
    }

    public function testAdherentIsNotGrantedWhenAlreadyHasProjectInInvalidStatus()
    {
        $adherent = $this->getAdherentMock(false);

        $this->assertRepositoryBehavior($adherent);
        $this->assertGrantedForAdherent(false, true, $adherent, CitizenProjectPermissions::CREATE);
    }

    public function testAdherentIsGranted()
    {
        $adherent = $this->getAdherentMock(false);

        $this->assertRepositoryBehavior($adherent, true);
        $this->assertGrantedForAdherent(true, true, $adherent, CitizenProjectPermissions::CREATE);
    }

    /**
     * @param bool|null $isAdministrator
     *
     * @return Adherent|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAdherentMock(bool $isReferent, bool $isAdministrator = false): Adherent
    {
        $adherent = $this->createAdherentMock();

        $adherent->expects($this->once())
            ->method('isReferent')
            ->willReturn($isReferent)
        ;

        if ($isReferent) {
            $adherent->expects($this->never())
                ->method('isProjectAdministrator')
            ;
        } else {
            $adherent->expects($this->once())
                ->method('isProjectAdministrator')
                ->willReturn($isAdministrator)
            ;
        }

        return $adherent;
    }

    private function assertRepositoryBehavior(?Adherent $adherent, bool $allowedToCreate = false): void
    {
        if ($adherent) {
            $this->projectRepository->expects($this->once())
                ->method('hasCitizenProjectInStatus')
                ->with($adherent, CitizenProject::STATUSES_NOT_ALLOWED_TO_CREATE)
                ->willReturn(!$allowedToCreate)
            ;
        } else {
            $this->projectRepository->expects($this->never())
                ->method('hasCitizenProjectInStatus')
            ;
        }
    }
}
