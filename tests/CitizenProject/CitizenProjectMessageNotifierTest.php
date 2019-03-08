<?php

namespace Tests\AppBundle\CitizenProject;

use AppBundle\CitizenProject\CitizenProjectFollowerAddedEvent;
use AppBundle\CitizenProject\CitizenProjectMessageNotifier;
use AppBundle\CitizenProject\CitizenProjectWasApprovedEvent;
use AppBundle\CitizenProject\CitizenProjectWasCreatedEvent;
use AppBundle\Collection\AdherentCollection;
use AppBundle\Committee\CommitteeManager;
use AppBundle\DataFixtures\ORM\LoadCitizenProjectData;
use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Mailer\MailerService;
use AppBundle\Repository\AdherentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\RouterInterface;

/**
 * @group citizenProject
 */
class CitizenProjectMessageNotifierTest extends TestCase
{
    private $adherentRepository;

    public function testOnCitizenProjectApprove()
    {
        $mailer = $this->createMock(MailerService::class);
        $citizenProjectWasApprovedEvent = $this->createMock(CitizenProjectWasApprovedEvent::class);
        $committeeManager = $this->createMock(CommitteeManager::class);
        $router = $this->createMock(RouterInterface::class);

        $citizenProject = $this->createCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID, 'Paris 8e');
        $citizenProject->expects($this->once())->method('getPendingCommitteeSupports')->willReturn(new ArrayCollection());

        $administrator = $this->createAdministrator(LoadAdherentData::ADHERENT_3_UUID);
        $citizenProjectWasApprovedEvent->expects($this->any())->method('getCitizenProject')->willReturn($citizenProject);
        $mailer->expects($this->once())->method('sendMessage');
        $manager = $this->createManager($administrator);

        $citizenProjectMessageNotifier = new CitizenProjectMessageNotifier($this->adherentRepository, $manager, $mailer, $committeeManager, $router);
        $citizenProjectMessageNotifier->onCitizenProjectApprove($citizenProjectWasApprovedEvent);
    }

    public function testOnCitizenProjectCreation()
    {
        $citizenProject = $this->createCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID, 'Paris 8e');
        $administrator = $this->createAdministrator(LoadAdherentData::ADHERENT_3_UUID);
        $manager = $this->createManager($administrator);

        $mailer = $this->createMock(MailerService::class);
        $citizenProjectWasCreatedEvent = $this->createMock(CitizenProjectWasCreatedEvent::class);
        $committeeManager = $this->createMock(CommitteeManager::class);
        $router = $this->createMock(RouterInterface::class);
        $coordinator = $this->createMock(Adherent::class);

        $citizenProjectWasCreatedEvent->expects($this->once())
            ->method('getCitizenProject')
            ->willReturn($citizenProject)
        ;

        $citizenProjectWasCreatedEvent->expects($this->once())
            ->method('getCreator')
            ->willReturn($administrator)
        ;

        $router->expects($this->exactly(2))->method('generate')->willReturn('http://foobar.io');
        $mailer->expects($this->exactly(2))->method('sendMessage');

        $this->adherentRepository->expects($this->once())
            ->method('findCoordinatorsByCitizenProject')
            ->willReturn(new AdherentCollection([$coordinator]))
        ;

        $citizenProjectMessageNotifier = new CitizenProjectMessageNotifier(
            $this->adherentRepository,
            $manager,
            $mailer,
            $committeeManager,
            $router
        );

        $citizenProjectMessageNotifier->onCitizenProjectCreation($citizenProjectWasCreatedEvent);
    }

    public function testSendAdherentNotificationCreation()
    {
        $mailer = $this->createMock(MailerService::class);
        $manager = $this->createManager();
        $adherent = $this->createMock(Adherent::class);
        $creator = $this->createMock(Adherent::class);
        $citizenProject = $this->createCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID, 'Paris 8e');
        $committeeManager = $this->createMock(CommitteeManager::class);
        $router = $this->createMock(RouterInterface::class);

        $mailer->expects($this->once())->method('sendMessage');

        $citizenProjectMessageNotifier = new CitizenProjectMessageNotifier($this->adherentRepository, $manager, $mailer, $committeeManager, $router);
        $citizenProjectMessageNotifier->sendAdherentNotificationCreation($adherent, $citizenProject, $creator);
    }

    public function testSendAdminitratorNotificationWhenFollowerAdded()
    {
        $mailer = $this->createMock(MailerService::class);
        $manager = $this->createManager();
        $adherent = $this->createMock(Adherent::class);
        $citizenProject = $this->createCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID, 'Paris 8e');
        $committeeManager = $this->createMock(CommitteeManager::class);
        $router = $this->createMock(RouterInterface::class);
        $administrator = $this->createAdministrator(LoadAdherentData::COMMITTEE_1_UUID);

        $manager->expects($this->once())->method('getCitizenProjectAdministrators')->willReturn(new AdherentCollection([$administrator]));
        $mailer->expects($this->once())->method('sendMessage');

        $citizenProjectMessageNotifier = new CitizenProjectMessageNotifier($this->adherentRepository, $manager, $mailer, $committeeManager, $router);
        $followerAddedEvent = new CitizenProjectFollowerAddedEvent($citizenProject, $adherent);
        $citizenProjectMessageNotifier->onCitizenProjectFollowerAdded($followerAddedEvent);
    }

    public function testSendAdminitratorNotificationWhenFollowerAddedWithAdministratorsInCitizenProject()
    {
        $mailer = $this->createMock(MailerService::class);
        $manager = $this->createManager();
        $adherent = $this->createMock(Adherent::class);
        $citizenProject = $this->createCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID, 'Paris 8e');
        $committeeManager = $this->createMock(CommitteeManager::class);
        $router = $this->createMock(RouterInterface::class);

        $manager->expects($this->once())->method('getCitizenProjectAdministrators')->willReturn(new AdherentCollection());
        $mailer->expects($this->never())->method('sendMessage');

        $citizenProjectMessageNotifier = new CitizenProjectMessageNotifier($this->adherentRepository, $manager, $mailer, $committeeManager, $router);
        $followerAddedEvent = new CitizenProjectFollowerAddedEvent($citizenProject, $adherent);
        $citizenProjectMessageNotifier->onCitizenProjectFollowerAdded($followerAddedEvent);
    }

    private function createCitizenProject(string $uuid, string $cityName): CitizenProject
    {
        $citizenProjectUuid = Uuid::fromString($uuid);

        $citizenProject = $this->createMock(CitizenProject::class);
        $citizenProject
            ->expects($this->any())
            ->method('getUuid')
            ->willReturn($citizenProjectUuid)
        ;
        $citizenProject
            ->expects($this->any())
            ->method('getCityName')
            ->willReturn($cityName)
        ;

        return $citizenProject;
    }

    private function createAdministrator(string $uuid): Adherent
    {
        $administratorUuid = Uuid::fromString($uuid);

        $administrator = $this->createMock(Adherent::class);
        $administrator->expects($this->any())->method('getUuid')->willReturn($administratorUuid);

        return $administrator;
    }

    private function createAuthor(): Adherent
    {
        $administrator = $this->createMock(Adherent::class);
        $administrator
            ->expects($this->any())
            ->method('getFirstName')
            ->willReturn('Pierre')
        ;

        return $administrator;
    }

    private function createManager(?Adherent $administrator = null, ?Adherent $member = null): CitizenProjectManager
    {
        $manager = $this->createMock(CitizenProjectManager::class);

        if ($administrator) {
            $manager->expects($this->any())->method('getCitizenProjectCreator')->willReturn($administrator);
        }

        if ($member) {
            $membres = new AdherentCollection();
            $membres->add($member);
            $manager
                ->expects($this->any())
                ->method('getCitizenProjectMembers')
                ->willReturn($membres)
            ;
        }

        return $manager;
    }

    protected function setUp()
    {
        parent::setUp();

        $this->adherentRepository = $this->createMock(AdherentRepository::class);
    }

    protected function tearDown()
    {
        $this->adherentRepository = null;

        parent::tearDown();
    }
}
