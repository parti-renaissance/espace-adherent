<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\CommitteeApprovalConfirmationMessage;
use AppBundle\Mailer\Message\CommitteeNewFollowerMessage;

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

    public function approve(Committee $committee)
    {
        $this->manager->approveCommittee($committee);

        $this->mailer->sendMessage(CommitteeApprovalConfirmationMessage::create(
            $this->manager->getCommitteeCreator($committee),
            $committee->getCityName(),
            $this->urlGenerator->getUrl('app_committee_show', $committee)
        ));
    }

    public function preApprove(Committee $committee)
    {
        $this->manager->preApproveCommittee($committee);
    }

    public function refuse(Committee $committee)
    {
        $this->manager->refuseCommittee($committee);
    }

    public function preRefuse(Committee $committee)
    {
        $this->manager->preRefuseCommittee($committee);
    }

    public function followCommittee(Adherent $adherent, Committee $committee)
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
