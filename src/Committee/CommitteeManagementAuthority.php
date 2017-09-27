<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\CommitteeApprovalConfirmationMessage;
use AppBundle\Mailer\Message\CommitteeNewFollowerMessage;
use AppBundle\Mailer\Message\CommitteeApprovalReferentMessage;

class CommitteeManagementAuthority
{
    private $manager;
    private $mailer;
    private $urlGenerator;

    public function __construct(
        CommitteeManager $manager,
        CommitteeUrlGenerator $urlGenerator,
        MailerService $mailer
    ) {
        $this->manager = $manager;
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
    }

    public function approve(Committee $committee): void
    {
        $this->manager->approveCommittee($committee);

        $animator = $this->manager->getCommitteeCreator($committee);

        $this->mailer->sendMessage(CommitteeApprovalConfirmationMessage::create(
            $animator,
            $committee->getCityName(),
            $this->urlGenerator->getUrl('app_committee_show', $committee)
        ));

        if (!$referent = $this->manager->getCommitteeReferent($committee)) {
            return;
        }

        $this->mailer->sendMessage(CommitteeApprovalReferentMessage::create(
            $referent,
            $animator,
            $committee,
            $this->urlGenerator->generate('app_adherent_contact', [
                'uuid' => (string) $animator->getUuid(),
                'from' => 'committee',
                'id' => (string) $committee->getUuid(),
            ])
        ));
    }

    public function preApprove(Committee $committee): void
    {
        $this->manager->preApproveCommittee($committee);
    }

    public function refuse(Committee $committee): void
    {
        $this->manager->refuseCommittee($committee);
    }

    public function preRefuse(Committee $committee): void
    {
        $this->manager->preRefuseCommittee($committee);
    }

    public function followCommittee(Adherent $adherent, Committee $committee): void
    {
        $this->manager->followCommittee($adherent, $committee);

        if (!$hosts = $this->manager->getCommitteeHosts($committee)->toArray()) {
            return;
        }

        $this->mailer->sendMessage(CommitteeNewFollowerMessage::create(
            $committee,
            $hosts,
            $adherent,
            $this->urlGenerator->getUrl('app_commitee_manager_list_members', $committee)
        ));
    }
}
