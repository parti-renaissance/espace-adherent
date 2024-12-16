<?php

namespace App\Committee;

use App\Entity\Committee;
use App\Mailer\MailerService;
use App\Mailer\Message\CommitteeApprovalConfirmationMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommitteeManagementAuthority
{
    public function __construct(
        private readonly CommitteeManager $manager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly MailerService $transactionalMailer,
    ) {
    }

    public function approve(Committee $committee): void
    {
        $this->manager->approveCommittee($committee);

        $this->transactionalMailer->sendMessage(CommitteeApprovalConfirmationMessage::create(
            $committee->getProvisionalSupervisors()->toArray(),
            $committee->getCityName(),
            $this->urlGenerator->generate('app_committee_show', ['slug' => $committee->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
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
}
