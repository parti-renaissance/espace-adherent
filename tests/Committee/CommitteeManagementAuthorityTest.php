<?php

namespace Tests\App\Committee;

use App\Committee\CommitteeManagementAuthority;
use App\Committee\CommitteeManager;
use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCommitteeV1Data;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\ProvisionalSupervisor;
use App\Mailer\MailerService;
use App\Mailer\Message\CommitteeApprovalConfirmationMessage;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Group('committee')]
class CommitteeManagementAuthorityTest extends TestCase
{
    public function testApprove()
    {
        $creator = $this->createCreator(LoadAdherentData::ADHERENT_3_UUID);
        $committee = $this->createCommittee(LoadCommitteeV1Data::COMMITTEE_1_UUID, 'Paris 8e', $creator);

        $manager = $this->createManager($creator);
        // ensure committee is approved
        $manager->expects($this->once())->method('approveCommittee')->with($committee);

        $mailer = $this->createMock(MailerService::class);
        $mailer->expects($this->once())
            ->method('sendMessage')
            ->with($this->isInstanceOf(CommitteeApprovalConfirmationMessage::class))
        ;

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->once())->method('generate')->willReturn(\sprintf(
            '/comites/%s',
            'comite-lille-beach'
        ));

        $committeeManagementAuthority = new CommitteeManagementAuthority($manager, $urlGenerator, $mailer, $this->createMock(EventDispatcherInterface::class));
        $committeeManagementAuthority->approve($committee);
    }

    public function testNotifyReferentsForApprovalWithMultipleReferents()
    {
        $mailer = $this->createMock(MailerService::class);
        // ensure no mail is sent
        $mailer->expects($this->never())->method('sendMessage')->with($this->anything());

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->never())->method('generate')->with($this->anything());
    }

    private function createCommittee(string $uuid, string $cityName, Adherent $adherent): Committee
    {
        $committeeUuid = Uuid::fromString($uuid);

        $committee = $this->createMock(Committee::class);
        $committee->expects($this->any())->method('getUuid')->willReturn($committeeUuid);
        $committee->expects($this->any())->method('getCityName')->willReturn($cityName);
        $provisionalSupervisor = new ProvisionalSupervisor($adherent, $committee);
        $committee->expects($this->any())->method('getProvisionalSupervisors')->willReturn(new ArrayCollection([$provisionalSupervisor]));

        return $committee;
    }

    private function createCreator(string $uuid): Adherent
    {
        $creatorUuid = Uuid::fromString($uuid);

        $creator = $this->createMock(Adherent::class);
        $creator->expects($this->any())->method('getUuid')->willReturn($creatorUuid);

        return $creator;
    }

    private function createManager(?Adherent $creator = null): CommitteeManager
    {
        $manager = $this->createMock(CommitteeManager::class);

        if ($creator) {
            $manager->expects($this->any())->method('getCommitteeCreator')->willReturn($creator);
        }

        return $manager;
    }
}
