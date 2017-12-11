<?php

namespace Tests\AppBundle\CitizenProject;

use AppBundle\CitizenProject\CitizenProjectMessageNotifier;
use AppBundle\CitizenProject\CitizenProjectWasApprovedEvent;
use AppBundle\CitizenProject\CitizenProjectWasCreatedEvent;
use AppBundle\Committee\CommitteeManager;
use AppBundle\DataFixtures\ORM\LoadCitizenProjectData;
use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Mailer\MailerService;
use Doctrine\Common\Collections\ArrayCollection;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\RouterInterface;

/**
 * @group functional
 */
class CitizenProjectMessageNotifierTest extends TestCase
{
    public function testOnCitizenProjectApprove()
    {
        $producer = $this->createMock(ProducerInterface::class);
        $mailer = $this->createMock(MailerService::class);
        $citizenProjectWasApprovedEvent = $this->createMock(CitizenProjectWasApprovedEvent::class);
        $committeeManager = $this->createMock(CommitteeManager::class);
        $router = $this->createMock(RouterInterface::class);

        $producer->expects($this->once())->method('publish')->with(\GuzzleHttp\json_encode([
            'uuid' => LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID,
            'offset' => 0,
        ]));

        $citizenProject = $this->createCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID, 'Paris 8e');
        $citizenProject->expects($this->once())->method('getPendingCommitteeSupports')->willReturn(new ArrayCollection());

        $administrator = $this->createAdministrator(LoadAdherentData::ADHERENT_3_UUID);
        $citizenProjectWasApprovedEvent->expects($this->any())->method('getCitizenProject')->willReturn($citizenProject);
        $mailer->expects($this->once())->method('sendMessage');
        $manager = $this->createManager($administrator);

        $citizenProjectMessageNotifier = new CitizenProjectMessageNotifier($producer, $manager, $mailer, $committeeManager, $router);
        $citizenProjectMessageNotifier->onCitizenProjectApprove($citizenProjectWasApprovedEvent);
    }

    public function testOnCitizenProjectCreation()
    {
        $producer = $this->createMock(ProducerInterface::class);
        $mailer = $this->createMock(MailerService::class);
        $citizenProjectWasCreatedEvent = $this->createMock(CitizenProjectWasCreatedEvent::class);
        $committeeManager = $this->createMock(CommitteeManager::class);
        $router = $this->createMock(RouterInterface::class);

        $citizenProject = $this->createCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID, 'Paris 8e');
        $administrator = $this->createAdministrator(LoadAdherentData::ADHERENT_3_UUID);
        $citizenProjectWasCreatedEvent->expects($this->once())->method('getCitizenProject')->willReturn($citizenProject);
        $citizenProjectWasCreatedEvent->expects($this->once())->method('getCreator')->willReturn($administrator);

        $mailer->expects($this->once())->method('sendMessage');
        $manager = $this->createManager($administrator);

        $citizenProjectMessageNotifier = new CitizenProjectMessageNotifier($producer, $manager, $mailer, $committeeManager, $router);
        $citizenProjectMessageNotifier->onCitizenProjectCreation($citizenProjectWasCreatedEvent);
    }

    public function testSendAdherentNotificationCreation()
    {
        $producer = $this->createMock(ProducerInterface::class);
        $mailer = $this->createMock(MailerService::class);
        $manager = $this->createManager();
        $adherent = $this->createMock(Adherent::class);
        $creator = $this->createMock(Adherent::class);
        $citizenProject = $this->createCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID, 'Paris 8e');
        $committeeManager = $this->createMock(CommitteeManager::class);
        $router = $this->createMock(RouterInterface::class);

        $mailer->expects($this->once())->method('sendMessage');

        $citizenProjectMessageNotifier = new CitizenProjectMessageNotifier($producer, $manager, $mailer, $committeeManager, $router);
        $citizenProjectMessageNotifier->sendAdherentNotificationCreation($adherent, $citizenProject, $creator);
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
