<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Mail\Transactional\CommitteeNewFollowerMail;
use AppBundle\Mail\Transactional\CommitteeApprovalConfirmationMail;
use AppBundle\Mail\Transactional\CommitteeApprovalReferentMail;
use EnMarche\MailerBundle\MailPost\MailPostInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommitteeManagementAuthority
{
    private $manager;
    private $mailPost;
    private $urlGenerator;

    public function __construct(
        CommitteeManager $manager,
        UrlGeneratorInterface $urlGenerator,
        MailPostInterface $mailPost
    ) {
        $this->manager = $manager;
        $this->urlGenerator = $urlGenerator;
        $this->mailPost = $mailPost;
    }

    public function approve(Committee $committee): void
    {
        $this->manager->approveCommittee($committee);

        $this->mailPost->address(
            CommitteeApprovalConfirmationMail::class,
            CommitteeApprovalConfirmationMail::createRecipientFrom($this->manager->getCommitteeCreator($committee)),
            null,
            CommitteeApprovalConfirmationMail::createTemplateVars(
                $committee,
                $this->urlGenerator->generate(
                    'app_committee_show',
                    ['slug' => $committee->getSlug()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            ),
            CommitteeApprovalConfirmationMail::SUBJECT
        );
    }

    public function notifyReferentsForApproval(Committee $committee): void
    {
        $referents = $this->manager->getCommitteeReferents($committee);

        if ($referents->count() > 1) {
            throw new MultipleReferentsFoundException($referents);
        }

        if (0 === $referents->count()) {
            return;
        }

        $animator = $this->manager->getCommitteeCreator($committee);
        $animatorLink = $this->urlGenerator->generate('app_adherent_contact', [
            'uuid' => (string) $animator->getUuid(),
            'from' => 'committee',
            'id' => (string) $committee->getUuid(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->mailPost->address(
            CommitteeApprovalReferentMail::class,
            CommitteeApprovalReferentMail::createRecipientFrom($referents->first()),
            null,
            CommitteeApprovalReferentMail::createTemplateVars($committee, $animator, $animatorLink),
            CommitteeApprovalReferentMail::SUBJECT
        );
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

        $hosts = $this->manager->getCommitteeHosts($committee);

        if ($hosts->isEmpty()) {
            return;
        }

        $url = $this->urlGenerator->generate('app_committee_manager_list_members', [
            'slug' => $committee->getSlug(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->mailPost->address(
            CommitteeNewFollowerMail::class,
            CommitteeNewFollowerMail::createRecipientsFrom($hosts),
            null,
            CommitteeNewFollowerMail::createTemplateVarsFrom($committee, $adherent, $url),
            CommitteeNewFollowerMail::SUBJECT
        );
    }
}
