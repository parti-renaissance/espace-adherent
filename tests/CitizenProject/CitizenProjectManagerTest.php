<?php

namespace Tests\AppBundle\CitizenProject;

use AppBundle\CitizenProject\CitizenProjectMessageNotifier;
use AppBundle\Collection\AdherentCollection;
use AppBundle\DataFixtures\ORM\LoadCitizenProjectData;
use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectMembership;
use AppBundle\Membership\AdherentEmailSubscription;
use Ramsey\Uuid\Uuid;
use Tests\AppBundle\MysqlWebTestCase;
use Tests\AppBundle\TestHelperTrait;

/**
 * @group functional
 */
class CitizenProjectManagerTest extends MysqlWebTestCase
{
    use TestHelperTrait;

    /* @var CitizenProjectManager */
    private $citizenProjectManager;

    public function testGetCitizenProjectAdministrators()
    {
        $this->assertInstanceOf(
            AdherentCollection::class,
            $administrators = $this->citizenProjectManager->getCitizenProjectAdministrators($this->getCitizenProjectMock(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID))
        );

        // Approved citizen projects
        $this->assertCount(2, $administrators);
        $this->assertCount(2, $this->citizenProjectManager->getCitizenProjectAdministrators($this->getCitizenProjectMock(LoadCitizenProjectData::CITIZEN_PROJECT_3_UUID)));
        $this->assertCount(1, $this->citizenProjectManager->getCitizenProjectAdministrators($this->getCitizenProjectMock(LoadCitizenProjectData::CITIZEN_PROJECT_4_UUID)));
        $this->assertCount(1, $this->citizenProjectManager->getCitizenProjectAdministrators($this->getCitizenProjectMock(LoadCitizenProjectData::CITIZEN_PROJECT_5_UUID)));

        // Unapproved citizen projects
        $this->assertCount(1, $this->citizenProjectManager->getCitizenProjectAdministrators($this->getCitizenProjectMock(LoadCitizenProjectData::CITIZEN_PROJECT_2_UUID)));
    }

    public function testGetCitizenProjectFollowers()
    {
        $citizenProject = $this->getCitizenProjectMock(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);
        $this->assertInstanceOf(AdherentCollection::class, $administrators = $this->citizenProjectManager->getCitizenProjectAdministrators($citizenProject));

        // Approved citizen projects
        $this->assertCount(2, $administrators);
        $this->assertCount(2, $this->citizenProjectManager->getCitizenProjectFollowers($citizenProject));

        $citizenProject = $this->getCitizenProjectMock(LoadCitizenProjectData::CITIZEN_PROJECT_3_UUID);
        $this->assertCount(2, $this->citizenProjectManager->getCitizenProjectAdministrators($citizenProject));
        $this->assertCount(0, $this->citizenProjectManager->getCitizenProjectFollowers($citizenProject));

        $citizenProject = $this->getCitizenProjectMock(LoadCitizenProjectData::CITIZEN_PROJECT_4_UUID);
        $this->assertCount(1, $this->citizenProjectManager->getCitizenProjectAdministrators($citizenProject));
        $this->assertCount(1, $this->citizenProjectManager->getCitizenProjectFollowers($citizenProject));

        $citizenProject = $this->getCitizenProjectMock(LoadCitizenProjectData::CITIZEN_PROJECT_5_UUID);
        $this->assertCount(1, $this->citizenProjectManager->getCitizenProjectAdministrators($citizenProject));
        $this->assertCount(2, $this->citizenProjectManager->getCitizenProjectFollowers($citizenProject));

        // Unapproved citizen projects
        $this->assertCount(1, $this->citizenProjectManager->getCitizenProjectAdministrators($this->getCitizenProjectMock(LoadCitizenProjectData::CITIZEN_PROJECT_2_UUID)));
    }

    public function testGetAdherentCitizenProjects()
    {
        $adherent = $this->getAdherentRepository()->findByUuid(LoadAdherentData::ADHERENT_3_UUID);

        // Without any fixed limit.
        $this->assertCount(8, $citizenProjects = $this->citizenProjectManager->getAdherentCitizenProjects($adherent));
        $this->assertSame('Le projet citoyen à Paris 8', (string) $citizenProjects[0]);
        $this->assertSame('Formation en ligne ouverte à tous à Évry', (string) $citizenProjects[1]);
        $this->assertSame('Projet citoyen à New York City', (string) $citizenProjects[2]);
        $this->assertSame('Le projet citoyen à Dammarie-les-Lys', (string) $citizenProjects[3]);
        $this->assertSame('Massive Open Online Course', (string) $citizenProjects[4]);
        $this->assertSame('Formation en ligne ouverte à tous', (string) $citizenProjects[5]);
        $this->assertSame('Projet citoyen à Berlin', (string) $citizenProjects[6]);
        $this->assertSame('En Marche - Projet citoyen', (string) $citizenProjects[7]);
    }

    public function testChangePrivilegeNotDefinedPrivilege()
    {
        $adherent = $this->getAdherentRepository()->findByUuid(LoadAdherentData::ADHERENT_3_UUID);
        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid privilege WRONG_PRIVILEGE');

        $this->citizenProjectManager->changePrivilege($adherent, $citizenProject, 'WRONG_PRIVILEGE');
    }

    public function testChangePrivilege()
    {
        $adherent = $this->getAdherentRepository()->findByUuid(LoadAdherentData::ADHERENT_3_UUID);
        $adherent2 = $this->getAdherentRepository()->findByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);

        // Change privileges of the first member ADMINISTRATOR => FOLLOWER => ADMINISTRATOR
        $this->assertEquals(true, $adherent->getCitizenProjectMembershipFor($citizenProject)->isAdministrator());
        $this->assertEquals(false, $adherent->getCitizenProjectMembershipFor($citizenProject)->isFollower());

        $this->citizenProjectManager->changePrivilege($adherent, $citizenProject, CitizenProjectMembership::CITIZEN_PROJECT_FOLLOWER);

        $this->assertEquals(true, $adherent->getCitizenProjectMembershipFor($citizenProject)->isFollower());
        $this->assertEquals(false, $adherent->getCitizenProjectMembershipFor($citizenProject)->isAdministrator());

        $this->citizenProjectManager->changePrivilege($adherent, $citizenProject, CitizenProjectMembership::CITIZEN_PROJECT_ADMINISTRATOR);

        $this->assertEquals(true, $adherent->getCitizenProjectMembershipFor($citizenProject)->isAdministrator());
        $this->assertEquals(false, $adherent->getCitizenProjectMembershipFor($citizenProject)->isFollower());

        // Change privileges of the second member: FOLLOWER => ADMINISTRATOR
        $this->assertEquals(true, $adherent2->getCitizenProjectMembershipFor($citizenProject)->isFollower());
        $this->assertEquals(false, $adherent2->getCitizenProjectMembershipFor($citizenProject)->isAdministrator());

        $this->citizenProjectManager->changePrivilege($adherent2, $citizenProject, CitizenProjectMembership::CITIZEN_PROJECT_ADMINISTRATOR);

        $this->assertEquals(true, $adherent2->getCitizenProjectMembershipFor($citizenProject)->isAdministrator());
        $this->assertEquals(false, $adherent2->getCitizenProjectMembershipFor($citizenProject)->isFollower());
    }

    public function testFindAdherentNearCitizenProjectOrAcceptAllNotification()
    {
        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);
        $adherents = $this->citizenProjectManager->findAdherentNearCitizenProjectOrAcceptAllNotification($citizenProject);

        $this->assertSame(3, $adherents->count());

        $adherents = $this->citizenProjectManager->findAdherentNearCitizenProjectOrAcceptAllNotification($citizenProject, 0, false);

        $this->assertSame(4, $adherents->count());

        $adherent = $this->getAdherentRepository()->findByEmail('francis.brioul@yahoo.com');
        $adherent->setCitizenProjectCreationEmailSubscriptionRadius(AdherentEmailSubscription::DISTANCE_100KM);

        $adherent = $this->getAdherentRepository()->findByEmail('referent@en-marche-dev.fr');
        $adherent->setCitizenProjectCreationEmailSubscriptionRadius(AdherentEmailSubscription::DISTANCE_100KM);

        $this->getManagerRegistry()->getManager()->flush();
        $this->getManagerRegistry()->getManager()->clear();

        $adherents = $this->citizenProjectManager->findAdherentNearCitizenProjectOrAcceptAllNotification($citizenProject, 0, true, CitizenProjectMessageNotifier::RADIUS_NOTIFICATION_NEAR_PROJECT_CITIZEN);

        $this->assertSame(5, $adherents->count());
    }

    private function getCitizenProjectMock(string $uuid)
    {
        $mock = $this->createMock(CitizenProject::class);
        $mock
            ->expects($this->any())
            ->method('getUuid')
            ->willReturn(Uuid::fromString($uuid))
        ;

        return $mock;
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
            LoadCitizenProjectData::class,
        ]);

        $this->container = $this->getContainer();
        $this->citizenProjectManager = new CitizenProjectManager($this->getManagerRegistry());
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->container = null;
        $this->citizenProjectManager = null;

        parent::tearDown();
    }
}
