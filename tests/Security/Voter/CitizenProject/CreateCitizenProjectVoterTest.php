<?php

namespace Tests\App\Security\Voter\CitizenProject;

use App\CitizenProject\CitizenProjectPermissions;
use App\Entity\Adherent;
use App\Entity\CitizenProject;
use App\Repository\CitizenProjectRepository;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\CitizenProject\CreateCitizenProjectVoter;
use Tests\App\Security\Voter\AbstractAdherentVoterTest;

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

    public function testNonAdherentIsNotGranted()
    {
        $adherent = $this->getAdherentMock(false);

        $this->assertRepositoryBehavior(null);
        $this->assertGrantedForAdherent(false, true, $adherent, CitizenProjectPermissions::CREATE);
    }

    public function testAdherentWithAPendingProjectIsNotGranted()
    {
        $adherent = $this->getAdherentMock(true);

        $this->assertRepositoryBehavior($adherent, false);
        $this->assertGrantedForAdherent(false, true, $adherent, CitizenProjectPermissions::CREATE);
    }

    public function testAdherentIsGrantedWhenAlreadyProjectAdministrator()
    {
        $adherent = $this->getAdherentMock(true);

        $this->assertRepositoryBehavior($adherent, true);
        $this->assertGrantedForAdherent(true, true, $adherent, CitizenProjectPermissions::CREATE);
    }

    public function testAdherentIsGranted()
    {
        $adherent = $this->getAdherentMock(true);

        $this->assertRepositoryBehavior($adherent, true);
        $this->assertGrantedForAdherent(true, true, $adherent, CitizenProjectPermissions::CREATE);
    }

    public function testReferentIsGranted()
    {
        $adherent = $this->getAdherentMock(true);

        $this->assertRepositoryBehavior($adherent, true);
        $this->assertGrantedForAdherent(true, true, $adherent, CitizenProjectPermissions::CREATE);
    }

    /**
     * @return Adherent|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAdherentMock(bool $isAdherent): Adherent
    {
        $adherent = $this->createAdherentMock();

        $adherent->expects($isAdherent ? $this->once() : $this->any())
            ->method('isAdherent')
            ->willReturn($isAdherent)
        ;

        $adherent->expects($this->never())
            ->method('isReferent')
        ;

        $adherent->expects($this->never())
            ->method('isCitizenProjectAdministrator')
        ;

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
