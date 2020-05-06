<?php

namespace Tests\App\CitizenProject;

use App\CitizenProject\CitizenProjectManagementAuthority;
use App\CitizenProject\CitizenProjectManager;
use App\CitizenProject\CitizenProjectWasApprovedEvent;
use App\CitizenProject\CitizenProjectWasUpdatedEvent;
use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCitizenProjectData;
use App\Entity\Adherent;
use App\Entity\CitizenProject;
use App\Events;
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
