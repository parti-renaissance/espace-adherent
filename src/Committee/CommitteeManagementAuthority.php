<?php

namespace App\Committee;

use App\Committee\Event\FollowCommitteeEvent;
use App\Committee\Exception\MultipleReferentsFoundException;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Mailer\MailerService;
use App\Mailer\Message\CommitteeApprovalConfirmationMessage;
use App\Mailer\Message\CommitteeApprovalReferentMessage;
use App\Membership\UserEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CommitteeManagementAuthority
{
    private $manager;
    private $mailer;
    private $urlGenerator;
    private $dispatcher;

    public function __construct(
        CommitteeManager $manager,
        UrlGeneratorInterface $urlGenerator,
        MailerService $transactionalMailer,
        EventDispatcherInterface $dispatcher
    ) {
        $this->manager = $manager;
        $this->urlGenerator = $urlGenerator;
        $this->mailer = $transactionalMailer;
        $this->dispatcher = $dispatcher;
    }

    public function approve(Committee $committee): void
    {
        $this->manager->approveCommittee($committee);

        $this->mailer->sendMessage(CommitteeApprovalConfirmationMessage::create(
            $committee->getProvisionalSupervisors()->toArray(),
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
            $this->mailer->sendMessage(CommitteeApprovalReferentMessage::create(
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

        $this->dispatcher->dispatch(new FollowCommitteeEvent($adherent, $committee), UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE);
    }
}
