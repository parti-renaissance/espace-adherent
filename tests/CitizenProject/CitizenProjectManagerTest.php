<?php

namespace Tests\AppBundle\CitizenProject;

use AppBundle\CitizenProject\CitizenProjectAuthority;
use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\CitizenProject\CitizenProjectMessageNotifier;
use AppBundle\Collection\AdherentCollection;
use AppBundle\DataFixtures\ORM\LoadCitizenProjectData;
use AppBundle\Membership\CitizenProjectNotificationDistance;
use Liip\FunctionalTestBundle\Test\WebTestCase;
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

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->citizenProjectManager = new CitizenProjectManager(
            $this->getManagerRegistry(),
            $this->getStorage(),
            $this->createMock(CitizenProjectAuthority::class)
        );
    }

    protected function tearDown()
    {
        $this->citizenProjectManager = null;

        $this->kill();

        parent::tearDown();
    }
}
