<?php

namespace Tests\AppBundle\CitizenProject;

use AppBundle\DataFixtures\ORM\LoadCitizenProjectData;
use AppBundle\CitizenProject\CitizenProjectManagementAuthority;
use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\CitizenProjectApprovalConfirmationMessage;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @functional
 */
class CitizenProjectManagementAuthorityTest extends TestCase
{
    public function testApprove()
    {
        $citizenProject = $this->createCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID, 'Paris 8e');
        $administrator = $this->createAdministrator(LoadAdherentData::ADHERENT_3_UUID);

        $manager = $this->createManager($administrator);
        // ensure citizen project is approved
        $manager->expects($this->once())->method('approveCitizenProject')->with($citizenProject);

        $mailer = $this->createMock(MailerService::class);
        $mailer->expects($this->at(0))
            ->method('sendMessage')
            ->with($this->isInstanceOf(CitizenProjectApprovalConfirmationMessage::class));

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->any())->method('generate')->willReturn(sprintf(
            '/projets-citoyens/%s',
            'mooc-paris'
        ));

        $citizenProjectManagementAuthority = new CitizenProjectManagementAuthority($manager, $urlGenerator, $mailer);
        $citizenProjectManagementAuthority->approve($citizenProject);
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
