<?php

namespace Tests\AppBundle\Committee;

use AppBundle\Committee\CommitteeManagementAuthority;
use AppBundle\Committee\CommitteeManager;
use AppBundle\Committee\CommitteeUrlGenerator;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\CommitteeApprovalConfirmationMessage;
use AppBundle\Mailjet\Message\CommitteeApprovalReferentMessage;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class CommitteeManagementAuthorityTest extends TestCase
{
    public function testApprove()
    {
        $committeeUuid = $this->createMock(Uuid::class);
        $committeeUuid->expects($this->once())->method('__toString')->willReturn(LoadAdherentData::COMMITTEE_1_UUID);

        $committee = $this->createMock(Committee::class);
        $committee->expects($this->once())->method('getUuid')->willReturn($committeeUuid);
        $committee->expects($this->any())->method('getCityName')->willReturn('Lille-Beach');

        $animatorUuid = $this->createMock(Uuid::class);
        $animatorUuid->expects($this->once())->method('__toString')->willReturn(LoadAdherentData::ADHERENT_3_UUID);

        $animator = $this->createMock(Adherent::class);
        $animator->expects($this->once())->method('getUuid')->willReturn($animatorUuid);

        $referent = $this->createMock(Adherent::class);

        $manager = $this->createMock(CommitteeManager::class);
        $manager->expects($this->once())->method('approveCommittee')->with($committee);
        $manager->expects($this->once())->method('getCommitteeCreator')->willReturn($animator);
        $manager->expects($this->once())->method('getCommitteeReferent')->willReturn($referent);

        $mailer = $this->createMock(MailjetService::class);
        $mailer->expects($this->at(0))
            ->method('sendMessage')
            ->with($this->isInstanceOf(CommitteeApprovalConfirmationMessage::class));

        $mailer->expects($this->at(1))
            ->method('sendMessage')
            ->with($this->isInstanceOf(CommitteeApprovalReferentMessage::class));

        $urlGenerator = $this->createMock(CommitteeUrlGenerator::class);
        $urlGenerator->expects($this->once())->method('getUrl')->willReturn(sprintf(
            '/comites/%s/%s',
            LoadAdherentData::COMMITTEE_1_UUID,
            'comite-lille-beach'
        ));
        $urlGenerator->expects($this->once())->method('generate')->willReturn(sprintf(
            '/espace-adherent/contacter/%s?from=%s&id=%s',
            LoadAdherentData::ADHERENT_3_UUID,
            'committee',
            LoadAdherentData::COMMITTEE_1_UUID
        ));

        $committeeManagementAuthority = new CommitteeManagementAuthority($manager, $urlGenerator, $mailer);
        $committeeManagementAuthority->approve($committee);
    }
}
