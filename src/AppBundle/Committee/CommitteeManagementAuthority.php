<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Committee;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\CommitteeApprovalConfirmationMessage;

class CommitteeManagementAuthority
{
    private $manager;
    private $mailjet;
    private $urlGenerator;

    public function __construct(
        CommitteeManager $manager,
        CommitteeUrlGenerator $urlGenerator,
        MailjetService $mailjet
    ) {
        $this->manager = $manager;
        $this->mailjet = $mailjet;
        $this->urlGenerator = $urlGenerator;
    }

    public function approve(Committee $committee)
    {
        $this->manager->approveCommittee($committee);

        $this->mailjet->sendMessage(CommitteeApprovalConfirmationMessage::create(
            $this->manager->getCommitteeCreator($committee),
            $committee->getCityName(),
            $this->urlGenerator->getUrl('app_committee_show', $committee)
        ));
    }

    public function refuse(Committee $committee)
    {
        $this->manager->refuseCommittee($committee);
    }
}
