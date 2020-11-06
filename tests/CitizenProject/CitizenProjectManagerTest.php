<?php

namespace Tests\App\CitizenProject;

use App\CitizenProject\CitizenProjectAuthority;
use App\CitizenProject\CitizenProjectFollowerChangeEvent;
use App\CitizenProject\CitizenProjectManager;
use App\CitizenProject\CitizenProjectWasUpdatedEvent;
use App\Collection\AdherentCollection;
use App\DataFixtures\ORM\LoadCitizenProjectData;
use App\Entity\Adherent;
use App\Entity\CitizenProject;
use App\Events;
use Doctrine\ORM\EntityManagerInterface;
use League\Glide\Server;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group citizenProject
 */
class CitizenProjectManagerTest extends WebTestCase
{
    use ControllerTestTrait;

    /* @var CitizenProjectManager */
    private $citizenProjectManager;

    public function testGetCitizenProjectAdministrators()
    {
        $this->assertInstanceOf(
            AdherentCollection::class,
            $administrators = $this->citizenProjectManager->getCitizenProjectAdministrators($this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID))
        );

        // Approved citizen projects
        $this->assertCount(1, $administrators);
        $this->assertCount(2, $this->citizenProjectManager->getCitizenProjectAdministrators($this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_3_UUID)));
        $this->assertCount(1, $this->citizenProjectManager->getCitizenProjectAdministrators($this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_4_UUID)));
        $this->assertCount(1, $this->citizenProjectManager->getCitizenProjectAdministrators($this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_5_UUID)));

        // Unapproved citizen projects
        $this->assertCount(0, $this->citizenProjectManager->getCitizenProjectAdministrators($this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_2_UUID)));
    }

    public function testGetCitizenProjectFollowers()
    {
        $this->assertInstanceOf(
            AdherentCollection::class,
            $followers = $this->citizenProjectManager->getCitizenProjectFollowers($this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID))
        );

        // Approved citizen projects
        $this->assertCount(3, $followers);
        $this->assertCount(0, $this->citizenProjectManager->getCitizenProjectFollowers($this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_3_UUID)));
        $this->assertCount(1, $this->citizenProjectManager->getCitizenProjectFollowers($this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_4_UUID)));
        $this->assertCount(2, $this->citizenProjectManager->getCitizenProjectFollowers($this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_5_UUID)));

        // Unapproved citizen projects
        $this->assertCount(1, $this->citizenProjectManager->getCitizenProjectFollowers($this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_2_UUID)));
    }

    public function testFollowerAddedSuccessfully()
    {
        $citizenProject = $this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);
        $adherent = $this->createMock(Adherent::class);
        $adherent
            ->expects($this->once())
            ->method('followCitizenProject')
            ->with($citizenProject)
        ;

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->will($this->returnValueMap([
                [Events::CITIZEN_PROJECT_FOLLOWER_ADDED, new CitizenProjectFollowerChangeEvent($citizenProject, $adherent), Event::class],
                [Events::CITIZEN_PROJECT_UPDATED, new CitizenProjectWasUpdatedEvent($citizenProject), Event::class],
            ]))
        ;

        $citizenProjectManager = new CitizenProjectManager(
            $this->createMock(EntityManagerInterface::class),
            $this->getStorage(),
            $this->createMock(CitizenProjectAuthority::class),
            $eventDispatcher,
            $this->createMock(Server::class)
        );
        $citizenProjectManager->followCitizenProject($adherent, $citizenProject);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();

        $this->citizenProjectManager = new CitizenProjectManager(
            $this->getEntityManager(CitizenProject::class),
            $this->getStorage(),
            $this->createMock(CitizenProjectAuthority::class),
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(Server::class)
        );
    }

    protected function tearDown(): void
    {
        $this->citizenProjectManager = null;

        $this->kill();

        parent::tearDown();
    }
}
