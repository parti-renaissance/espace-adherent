<?php

namespace Tests\AppBundle\Security\Voter;

use AppBundle\CitizenAction\CitizenActionPermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Repository\CitizenProjectMembershipRepository;
use AppBundle\Security\Voter\AbstractAdherentVoter;
use AppBundle\Security\Voter\ManageCitizenActionVoter;
use Ramsey\Uuid\UuidInterface;

class ManageCitizenActionVoterTest extends AbstractAdherentVoterTest
{
    /**
     * @var CitizenProjectMembershipRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $membershipRepository;

    protected function setUp(): void
    {
        $this->membershipRepository = $this->createMock(CitizenProjectMembershipRepository::class);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->membershipRepository = null;

        parent::tearDown();
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new ManageCitizenActionVoter($this->membershipRepository);
    }

    public function provideAnonymousCases(): iterable
    {
        foreach (CitizenActionPermissions::MANAGE as $permission) {
            yield [false, true, $permission, $this->createMock(CitizenProject::class)];
        }
    }

    public function providePermissions(): iterable
    {
        foreach (CitizenActionPermissions::MANAGE as $permission) {
            yield [$permission];
        }
    }

    /**
     * @dataProvider providePermissions
     */
    public function testAdherentIsNotGrantedIfProjectIsNotApproved(string $attribute)
    {
        $project = $this->getCitizenProjectMock(false);
        $adherent = $this->getAdherentMock(false);

        // assert repository is not invoked
        $this->assertMembershipRepositoryMock();
        $this->assertGrantedForAdherent(false, true, $adherent, $attribute, $project);
    }

    /**
     * @dataProvider providePermissions
     */
    public function testAdherentIsGrantedIfCreator(string $attribute)
    {
        $project = $this->getCitizenProjectMock(true, true);
        $adherent = $this->getAdherentMock(true);

        // assert repository is not invoked
        $this->assertMembershipRepositoryMock();
        $this->assertGrantedForAdherent(true, true, $adherent, $attribute, $project);
    }

    /**
     * @dataProvider providePermissions
     */
    public function testAdherentIsGrantedIfAdministratorWithLoadedMemberships(string $attribute)
    {
        $project = $this->getCitizenProjectMock(true);
        $adherent = $this->getAdherentMock(true, true, true, $project);

        // assert repository is not invoked
        $this->assertMembershipRepositoryMock();
        $this->assertGrantedForAdherent(true, true, $adherent, $attribute, $project);
    }

    /**
     * @dataProvider providePermissions
     */
    public function testAdherentIsGrantedIfAdministratorWithoutLoadedMemberships(string $attribute)
    {
        $project = $this->getCitizenProjectMock(true, false, true);
        $adherent = $this->getAdherentMock(true, false, true, $project);

        // assert repository is invoked
        $this->assertMembershipRepositoryMock(true, $adherent);
        $this->assertGrantedForAdherent(true, true, $adherent, $attribute, $project);
    }

    /**
     * @dataProvider providePermissions
     */
    public function testAdherentIsNotGrantedIfAdministratorWithLoadedMemberships(string $attribute)
    {
        $project = $this->getCitizenProjectMock(true);
        $adherent = $this->getAdherentMock(true, true, false, $project);

        // assert repository is not invoked
        $this->assertMembershipRepositoryMock();
        $this->assertGrantedForAdherent(false, true, $adherent, $attribute, $project);
    }

    /**
     * @dataProvider providePermissions
     */
    public function testAdherentIsNotGrantedIfAdministratorWithoutLoadedMemberships(string $attribute)
    {
        $project = $this->getCitizenProjectMock(true, false, true);
        $adherent = $this->getAdherentMock(true, false, false, $project);

        // assert repository is invoked
        $this->assertMembershipRepositoryMock(false, $adherent);
        $this->assertGrantedForAdherent(false, true, $adherent, $attribute, $project);
    }

    /**
     * @param bool                $uuidChecked
     * @param bool|null           $hasLoadedMemberships
     * @param bool                $isAdministrator
     * @param CitizenProject|null $project
     *
     * @return Adherent|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAdherentMock(
        bool $uuidChecked = false,
        bool $hasLoadedMemberships = null,
        bool $isAdministrator = false,
        CitizenProject $project = null
    ): Adherent {
        $adherent = $this->createAdherentMock();

        $adherent->expects($uuidChecked ? $this->once() : $this->never())
            ->method('getUuid')
        ;

        if (null !== $hasLoadedMemberships) {
            if ($hasLoadedMemberships) {
                $adherent->expects($this->once())
                    ->method('hasLoadedCitizenProjectMemberships')
                    ->willReturn(true)
                ;
                $adherent->expects($this->once())
                    ->method('isAdministratorOf')
                    ->with($project)
                    ->willReturn($isAdministrator)
                ;
            } else {
                $adherent->expects($this->once())
                    ->method('hasLoadedCitizenProjectMemberships')
                    ->willReturn(false)
                ;
                $adherent->expects($this->never())
                    ->method('isAdministratorOf')
                ;
            }
        } else {
            $adherent->expects($this->never())
                ->method('hasLoadedCitizenProjectMemberships')
            ;
        }

        return $adherent;
    }

    /**
     * @param bool $approved
     * @param bool $fromCreator
     * @param bool $uuidChecked
     *
     * @return CitizenProject|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getCitizenProjectMock(bool $approved, bool $fromCreator = false, bool $uuidChecked = false): CitizenProject
    {
        $project = $this->createMock(CitizenProject::class);

        $project->expects($this->once())
            ->method('isApproved')
            ->willReturn($approved)
        ;

        if ($approved) {
            $project->expects($this->once())
                ->method('isCreatedBy')
                ->willReturn($fromCreator)
            ;
        }

        if ($uuidChecked) {
            $uuid = $this->createMock(UuidInterface::class);
            $uuid->expects($this->once())
                ->method('toString')
                ->willReturn('test')
            ;
            $project->expects($this->once())
                ->method('getUuid')
                ->willReturn($uuid)
            ;
        } else {
            $project->expects($this->never())
                ->method('getUuid')
            ;
        }

        return $project;
    }

    private function assertMembershipRepositoryMock(bool $hasAdministrator = null, Adherent $host = null): void
    {
        if (null !== $hasAdministrator) {
            $this->membershipRepository->expects($this->once())
                ->method('administrateCitizenProject')
                ->with($host, 'test')
                ->willReturn($hasAdministrator)
            ;
        } else {
            $this->membershipRepository->expects($this->never())
               ->method('administrateCitizenProject')
            ;
        }
    }
}
