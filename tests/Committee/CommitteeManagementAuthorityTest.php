<?php

namespace Tests\App\Committee;

use App\Collection\AdherentCollection;
use App\Committee\CommitteeManagementAuthority;
use App\Committee\CommitteeManager;
use App\Committee\MultipleReferentsFoundException;
use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCommitteeData;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\ProvisionalSupervisor;
use App\Mailer\MailerService;
use App\Mailer\Message\CommitteeApprovalConfirmationMessage;
use App\Mailer\Message\CommitteeApprovalReferentMessage;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @group committee
 */
class CommitteeManagementAuthorityTest extends TestCase
{
    public function testApprove()
    {
        $creator = $this->createCreator(LoadAdherentData::ADHERENT_3_UUID);
        $committee = $this->createCommittee(LoadCommitteeData::COMMITTEE_1_UUID, 'Paris 8e', $creator);

        $manager = $this->createManager($creator);
        // ensure committee is approved
        $manager->expects($this->once())->method('approveCommittee')->with($committee);

        $mailer = $this->createMock(MailerService::class);
        $mailer->expects($this->at(0))
            ->method('sendMessage')
            ->with($this->isInstanceOf(CommitteeApprovalConfirmationMessage::class))
        ;

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->at(0))->method('generate')->willReturn(sprintf(
            '/comites/%s',
            'comite-lille-beach'
        ));

        $committeeManagementAuthority = new CommitteeManagementAuthority($manager, $urlGenerator, $mailer, $this->createMock(EventDispatcherInterface::class));
        $committeeManagementAuthority->approve($committee);
    }

    public function testNotifyReferentsForApproval()
    {
        $creator = $this->createCreator(LoadAdherentData::ADHERENT_3_UUID);
        $committee = $this->createCommittee(LoadCommitteeData::COMMITTEE_1_UUID, 'Paris 8e', $creator);
        $referent = $this->createMock(Adherent::class);

        $referents = new AdherentCollection([$referent]);
        $manager = $this->createManager($creator, $referents);

        $mailer = $this->createMock(MailerService::class);
        $mailer->expects($this->at(0))
            ->method('sendMessage')
            ->with($this->isInstanceOf(CommitteeApprovalReferentMessage::class))
        ;

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->any())->method('generate')->willReturn(sprintf(
            '/espace-adherent/contacter/%s?from=%s&id=%s',
            LoadAdherentData::ADHERENT_3_UUID,
            'committee',
            LoadCommitteeData::COMMITTEE_1_UUID
        ));

        $committeeManagementAuthority = new CommitteeManagementAuthority($manager, $urlGenerator, $mailer, $this->createMock(EventDispatcherInterface::class));
        $committeeManagementAuthority->notifyReferentsForApproval($committee);
    }

    public function testNotifyReferentsForApprovalWithMultipleReferents()
    {
        $this->expectException(MultipleReferentsFoundException::class);
        $creator = $this->createCreator(LoadAdherentData::ADHERENT_3_UUID);
        $committee = $this->createCommittee(LoadCommitteeData::COMMITTEE_1_UUID, 'Paris 8e', $creator);
        $referent = $this->createMock(Adherent::class);

        $referents = new AdherentCollection([$referent, $creator]);

        $manager = $this->createManager($creator, $referents);

        $mailer = $this->createMock(MailerService::class);
        // ensure no mail is sent
        $mailer->expects($this->never())->method('sendMessage')->with($this->anything());

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->never())->method('generate')->with($this->anything());

        $committeeManagementAuthority = new CommitteeManagementAuthority($manager, $urlGenerator, $mailer, $this->createMock(EventDispatcherInterface::class));
        $committeeManagementAuthority->notifyReferentsForApproval($committee);
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

    private function createManager(?Adherent $creator = null, ?AdherentCollection $referents = null): CommitteeManager
    {
        $manager = $this->createMock(CommitteeManager::class);

        if ($creator) {
            $manager->expects($this->any())->method('getCommitteeCreator')->willReturn($creator);
        }

        if ($referents) {
            $manager->expects($this->any())->method('getCommitteeReferents')->willReturn($referents);
        }

        return $manager;
    }
}
