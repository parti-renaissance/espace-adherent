<?php

namespace App\VotingPlatform\Listener;

use App\Mailer\MailerService;
use App\Mailer\Message\CommitteeCandidacyCreatedConfirmationMessage;
use App\Mailer\Message\CommitteeCandidacyRemovedConfirmationMessage;
use App\Mailer\Message\CommitteeNewCandidacyNotificationMessage;
use App\Mailer\Message\CommitteeRemovedCandidacyNotificationMessage;
use App\Security\Http\Session\AnonymousFollowerSession;
use App\VotingPlatform\Event\BaseCandidacyEvent;
use App\VotingPlatform\Event\CommitteeCandidacyEvent;
use App\VotingPlatform\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommitteeCandidacySendEmailsListener implements EventSubscriberInterface
{
    private $mailer;
    private $urlGenerator;

    public function __construct(MailerService $transactionalMailer, UrlGeneratorInterface $urlGenerator)
    {
        $this->mailer = $transactionalMailer;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CANDIDACY_CREATED => 'onCandidacyCreated',
            Events::CANDIDACY_REMOVED => 'onCandidacyRemoved',
        ];
    }

    public function onCandidacyCreated(BaseCandidacyEvent $event): void
    {
        if (!$event instanceof CommitteeCandidacyEvent) {
            return;
        }

        $committee = $event->getCommittee();

        $this->mailer->sendMessage(CommitteeCandidacyCreatedConfirmationMessage::create(
            $event->getCandidate(),
            $committee->getCommitteeElection(),
            $this->urlGenerator->generate('app_committee_show', ['slug' => $committee->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
        ));

        $this->notifySupervisor($event, CommitteeNewCandidacyNotificationMessage::class);
    }

    public function onCandidacyRemoved(BaseCandidacyEvent $event): void
    {
        if (!$event instanceof CommitteeCandidacyEvent) {
            return;
        }

        $committee = $event->getCommittee();

        $this->mailer->sendMessage(CommitteeCandidacyRemovedConfirmationMessage::create(
            $event->getCandidate(),
            $committee->getCommitteeElection(),
            $this->urlGenerator->generate(
                'app_committee_show',
                [
                    'slug' => $committee->getSlug(),
                    '_fragment' => 'committee-toggle-candidacy',
                    AnonymousFollowerSession::AUTHENTICATION_INTENTION => '/connexion',
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        ));

        $this->notifySupervisor($event, CommitteeRemovedCandidacyNotificationMessage::class);
    }

    /**
     * @param CommitteeRemovedCandidacyNotificationMessage|CommitteeNewCandidacyNotificationMessage $messageClass
     */
    private function notifySupervisor(CommitteeCandidacyEvent $event, string $messageClass): void
    {
        if ($supervisor = $event->getSupervisor()) {
            $this->mailer->sendMessage(
                $messageClass::create(
                    $event->getCandidacy(),
                    $event->getCommittee()->getCommitteeElection(),
                    $supervisor,
                    $event->getCandidate(),
                    $this->urlGenerator->generate(
                        'app_committee_show',
                        ['slug' => $event->getCommittee()->getSlug()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                )
            );
        }
    }
}
