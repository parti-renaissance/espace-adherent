<?php

namespace Tests\AppBundle\CitizenProject;

use AppBundle\CitizenProject\CitizenProjectManagementAuthority;
use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\CitizenProject\CitizenProjectWasApprovedEvent;
use AppBundle\CitizenProject\CitizenProjectWasUpdatedEvent;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadCitizenProjectData;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Events;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @group citizenProject
 */
class CitizenProjectManagementAuthorityTest extends TestCase
{
    public function testApprove()
    {
        $citizenProject = $this->createCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID, 'Paris 8e');
        $administrator = $this->createAdministrator(LoadAdherentData::ADHERENT_3_UUID);

        $manager = $this->createManager($administrator);
        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->will($this->returnValueMap([
                [Events::CITIZEN_PROJECT_APPROVED, new CitizenProjectWasApprovedEvent($citizenProject), Event::class],
                [Events::CITIZEN_PROJECT_UPDATED, new CitizenProjectWasUpdatedEvent($citizenProject), Event::class],
            ]))
        ;

        // ensure citizen project is approved
        $manager->expects($this->once())->method('approveCitizenProject')->with($citizenProject);

        $citizenProjectManagementAuthority = new CitizenProjectManagementAuthority($manager, $eventDispatcher);
        $citizenProjectManagementAuthority->approve($citizenProject);
    }

    public function testRefuse()
    {
        $citizenProject = $this->createCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID, 'Paris 8e');
        $administrator = $this->createAdministrator(LoadAdherentData::ADHERENT_3_UUID);

        $manager = $this->createManager($administrator);
        $eventDispatcher = $this->createMock(EventDispatcher::class);

        // ensure citizen project is approved
        $manager->expects($this->once())->method('refuseCitizenProject')->with($citizenProject);

        $citizenProjectManagementAuthority = new CitizenProjectManagementAuthority($manager, $eventDispatcher);
        $citizenProjectManagementAuthority->refuse($citizenProject);
    }

    private function createCitizenProject(string $uuid, string $cityName): CitizenProject
    {
        $citizenProjectUuid = Uuid::fromString($uuid);

        $citizenProject = $this->createMock(CitizenProject::class);
        $citizenProject->expects($this->any())->method('getUuid')->willReturn($citizenProjectUuid);
        $citizenProject->expects($this->any())->method('getCityName')->willReturn($cityName);

        return $citizenProject;
    }

    private function createAdministrator(string $uuid): Adherent
    {
        $administratorUuid = Uuid::fromString($uuid);

        $administrator = $this->createMock(Adherent::class);
        $administrator->expects($this->any())->method('getUuid')->willReturn($administratorUuid);

        return $administrator;
    }

    private function createManager(?Adherent $administrator = null): CitizenProjectManager
    {
        $manager = $this->createMock(CitizenProjectManager::class);

        if ($administrator) {
            $manager->expects($this->any())->method('getCitizenProjectCreator')->willReturn($administrator);
        }

        return $manager;
    }
}
