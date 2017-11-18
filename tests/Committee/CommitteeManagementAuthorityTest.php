<?php

namespace Tests\AppBundle\Committee;

use AppBundle\Collection\AdherentCollection;
use AppBundle\Committee\CommitteeManagementAuthority;
use AppBundle\Committee\CommitteeManager;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\CommitteeApprovalConfirmationMessage;
use AppBundle\Mailer\Message\CommitteeApprovalReferentMessage;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @functional
 */
class CommitteeManagementAuthorityTest extends TestCase
{
    public function testApprove()
    {
        $committee = $this->createCommittee(LoadAdherentData::COMMITTEE_1_UUID, 'Paris 8e');
        $animator = $this->createAnimator(LoadAdherentData::ADHERENT_3_UUID);

        $manager = $this->createManager($committee, $animator);
        // ensure committee is approved
        $manager->expects($this->once())->method('approveCommittee')->with($committee);

        $mailer = $this->createMock(MailerService::class);
        $mailer->expects($this->at(0))
            ->method('sendMessage')
            ->with($this->isInstanceOf(CommitteeApprovalConfirmationMessage::class));

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $committeeManagementAuthority = new CommitteeManagementAuthority($manager, $urlGenerator, $mailer);
        $committeeManagementAuthority->approve($committee);
    }

    public function testNotifyReferentsForApproval()
    {
        $committee = $this->createCommittee(LoadAdherentData::COMMITTEE_1_UUID, 'Paris 8e');
        $animator = $this->createAnimator(LoadAdherentData::ADHERENT_3_UUID);
        $referent = $this->createMock(Adherent::class);

        $referents = new AdherentCollection([$referent]);
        $manager = $this->createManager($committee, $animator, $referents);

        $mailer = $this->createMock(MailerService::class);
        $mailer->expects($this->at(0))
            ->method('sendMessage')
            ->with($this->isInstanceOf(CommitteeApprovalReferentMessage::class));

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->any())->method('generate')->willReturn(sprintf(
            '/espace-adherent/contacter/%s?from=%s&id=%s',
            LoadAdherentData::ADHERENT_3_UUID,
            'committee',
            LoadAdherentData::COMMITTEE_1_UUID
        ));

        $committeeManagementAuthority = new CommitteeManagementAuthority($manager, $urlGenerator, $mailer);
        $committeeManagementAuthority->notifyReferentsForApproval($committee);
    }

    /**
     * @expectedException \AppBundle\Committee\MultipleReferentsFoundException
     */
    public function testNotifyReferentsForApprovalWithMultipleReferents()
    {
        $committee = $this->createCommittee(LoadAdherentData::COMMITTEE_1_UUID, 'Paris 8e');
        $animator = $this->createAnimator(LoadAdherentData::ADHERENT_3_UUID);
        $referent = $this->createMock(Adherent::class);

        $referents = new AdherentCollection([$referent, $animator]);

        $manager = $this->createManager($committee, $animator, $referents);

        $mailer = $this->createMock(MailerService::class);
        // ensure no mail is sent
        $mailer->expects($this->never())->method('sendMessage')->with($this->anything());

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->never())->method('generate')->with($this->anything());

        $committeeManagementAuthority = new CommitteeManagementAuthority($manager, $urlGenerator, $mailer);
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

    private function createManager(Committee $committee, ?Adherent $animator = null, ?AdherentCollection $referents = null): CommitteeManager
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
