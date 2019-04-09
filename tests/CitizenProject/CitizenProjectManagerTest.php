<?php

namespace Tests\AppBundle\CitizenProject;

use AppBundle\CitizenProject\CitizenProjectAuthority;
use AppBundle\CitizenProject\CitizenProjectFollowerChangeEvent;
use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\CitizenProject\CitizenProjectMessageNotifier;
use AppBundle\CitizenProject\CitizenProjectWasUpdatedEvent;
use AppBundle\Collection\AdherentCollection;
use AppBundle\DataFixtures\ORM\LoadCitizenProjectData;
use AppBundle\Entity\Adherent;
use AppBundle\Events;
use AppBundle\Membership\CitizenProjectNotificationDistance;
use Doctrine\Common\Persistence\ObjectManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\AppBundle\Controller\ControllerTestTrait;

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

    public function testFindAdherentNearCitizenProjectOrAcceptAllNotification()
    {
        $citizenProject = $this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);
        $adherents = $this->citizenProjectManager->findAdherentNearCitizenProjectOrAcceptAllNotification($citizenProject);

        $this->assertSame(6, $adherents->count());

        $adherents = $this->citizenProjectManager->findAdherentNearCitizenProjectOrAcceptAllNotification($citizenProject, 0, false);

        $this->assertSame(7, $adherents->count());

        $adherent = $this->getAdherentRepository()->findOneByEmail('francis.brioul@yahoo.com');
        $adherent->setCitizenProjectCreationEmailSubscriptionRadius(CitizenProjectNotificationDistance::DISTANCE_100KM);

        $adherent = $this->getAdherentRepository()->findOneByEmail('referent@en-marche-dev.fr');
        $adherent->setCitizenProjectCreationEmailSubscriptionRadius(CitizenProjectNotificationDistance::DISTANCE_100KM);

        $this->getManagerRegistry()->getManager()->flush();
        $this->getManagerRegistry()->getManager()->clear();

        $adherents = $this->citizenProjectManager->findAdherentNearCitizenProjectOrAcceptAllNotification($citizenProject, 0, true, CitizenProjectMessageNotifier::RADIUS_NOTIFICATION_NEAR_PROJECT_CITIZEN);

        $this->assertSame(8, $adherents->count());
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
            $this->createConfiguredMock(RegistryInterface::class, ['getManager' => $this->createMock(ObjectManager::class)]),
            $this->getStorage(),
            $this->createMock(CitizenProjectAuthority::class),
            $eventDispatcher
        );
        $citizenProjectManager->followCitizenProject($adherent, $citizenProject);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->citizenProjectManager = new CitizenProjectManager(
            $this->getManagerRegistry(),
            $this->getStorage(),
            $this->createMock(CitizenProjectAuthority::class),
            $this->createMock(EventDispatcherInterface::class)
        );
    }

    protected function tearDown()
    {
        $this->citizenProjectManager = null;

        $this->kill();

        parent::tearDown();
    }
}
