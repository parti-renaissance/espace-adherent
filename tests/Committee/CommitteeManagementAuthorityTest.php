<?php

namespace Tests\App\Committee;

use App\Collection\AdherentCollection;
use App\Committee\CommitteeManagementAuthority;
use App\Committee\CommitteeManager;
use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Mailer\MailerService;
use App\Mailer\Message\CommitteeApprovalConfirmationMessage;
use App\Mailer\Message\CommitteeApprovalReferentMessage;
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
        $committee = $this->createCommittee(LoadAdherentData::COMMITTEE_1_UUID, 'Paris 8e');
        $animator = $this->createAnimator(LoadAdherentData::ADHERENT_3_UUID);

        $manager = $this->createManager($animator);
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
        $committee = $this->createCommittee(LoadAdherentData::COMMITTEE_1_UUID, 'Paris 8e');
        $animator = $this->createAnimator(LoadAdherentData::ADHERENT_3_UUID);
        $referent = $this->createMock(Adherent::class);

        $referents = new AdherentCollection([$referent]);
        $manager = $this->createManager($animator, $referents);

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
            LoadAdherentData::COMMITTEE_1_UUID
        ));

        $committeeManagementAuthority = new CommitteeManagementAuthority($manager, $urlGenerator, $mailer, $this->createMock(EventDispatcherInterface::class));
        $committeeManagementAuthority->notifyReferentsForApproval($committee);
    }

    /**
     * @expectedException \App\Committee\MultipleReferentsFoundException
     */
    public function testNotifyReferentsForApprovalWithMultipleReferents()
    {
        $committee = $this->createCommittee(LoadAdherentData::COMMITTEE_1_UUID, 'Paris 8e');
        $animator = $this->createAnimator(LoadAdherentData::ADHERENT_3_UUID);
        $referent = $this->createMock(Adherent::class);

        $referents = new AdherentCollection([$referent, $animator]);

        $manager = $this->createManager($animator, $referents);

        $mailer = $this->createMock(MailerService::class);
        // ensure no mail is sent
        $mailer->expects($this->never())->method('sendMessage')->with($this->anything());

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->never())->method('generate')->with($this->anything());

        $committeeManagementAuthority = new CommitteeManagementAuthority($manager, $urlGenerator, $mailer, $this->createMock(EventDispatcherInterface::class));
        $committeeManagementAuthority->notifyReferentsForApproval($committee);
    }

    private function createCommittee(string $uuid, string $cityName): Committee
    {
        $committeeUuid = Uuid::fromString($uuid);

        $committee = $this->createMock(Committee::class);
        $committee->expects($this->any())->method('getUuid')->willReturn($committeeUuid);
        $committee->expects($this->any())->method('getCityName')->willReturn($cityName);

        return $committee;
    }

    private function createAnimator(string $uuid): Adherent
    {
        $animatorUuid = Uuid::fromString($uuid);

        $animator = $this->createMock(Adherent::class);
        $animator->expects($this->any())->method('getUuid')->willReturn($animatorUuid);

        return $animator;
    }

    private function createManager(?Adherent $animator = null, ?AdherentCollection $referents = null): CommitteeManager
    {
        $manager = $this->createMock(CommitteeManager::class);

        if ($animator) {
            $manager->expects($this->any())->method('getCommitteeCreator')->willReturn($animator);
        }

        if ($referents) {
            $manager->expects($this->any())->method('getCommitteeReferents')->willReturn($referents);
        }

        return $manager;
    }
}
