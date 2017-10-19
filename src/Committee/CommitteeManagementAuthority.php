<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\CommitteeApprovalConfirmationMessage;
use AppBundle\Mailjet\Message\CommitteeApprovalReferentMessage;
use AppBundle\Mailjet\Message\CommitteeNewFollowerMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommitteeManagementAuthority
{
    private $manager;
    private $mailjet;
    private $urlGenerator;

    public function __construct(
        CommitteeManager $manager,
        UrlGeneratorInterface $urlGenerator,
        MailjetService $mailjet
    ) {
        $this->manager = $manager;
        $this->mailjet = $mailjet;
        $this->urlGenerator = $urlGenerator;
    }

    public function approve(Committee $committee): void
    {
        $this->manager->approveCommittee($committee);

        $this->mailjet->sendMessage(CommitteeApprovalConfirmationMessage::create(
            $this->manager->getCommitteeCreator($committee),
            $committee->getCityName(),
            $this->urlGenerator->generate('app_committee_show', ['slug' => $committee->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }

    public function notifyReferentsForApproval(Committee $committee): void
    {
        $referents = $this->manager->getCommitteeReferents($committee);

        if ($referents->count() > 1) {
            throw new MultipleReferentsFoundException($referents);
        }

        $animator = $this->manager->getCommitteeCreator($committee);
        $animatorLink = $this->urlGenerator->generate('app_adherent_contact', [
            'uuid' => (string) $animator->getUuid(),
            'from' => 'committee',
            'id' => (string) $committee->getUuid(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        foreach ($referents as $referent) {
            $this->mailjet->sendMessage(CommitteeApprovalReferentMessage::create(
                $referent,
                $animator,
                $committee,
                $animatorLink
            ));
        }
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

        $this->mailjet->sendMessage(CommitteeNewFollowerMessage::create(
            $committee,
            $hosts,
            $adherent,
            $this->urlGenerator->generate('app_commitee_manager_list_members', ['slug' => $committee->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }
}
