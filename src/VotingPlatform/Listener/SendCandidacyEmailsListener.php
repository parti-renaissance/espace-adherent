<?php

namespace AppBundle\VotingPlatform\Listener;

use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\CommitteeCandidacyCreatedConfirmationMessage;
use AppBundle\Mailer\Message\CommitteeCandidacyRemovedConfirmationMessage;
use AppBundle\Mailer\Message\CommitteeNewCandidacyNotificationMessage;
use AppBundle\Mailer\Message\CommitteeRemovedCandidacyNotificationMessage;
use AppBundle\Security\Http\Session\AnonymousFollowerSession;
use AppBundle\VotingPlatform\Event\CommitteeCandidacyEvent;
use AppBundle\VotingPlatform\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendCandidacyEmailsListener implements EventSubscriberInterface
{
    private $mailer;
    private $urlGenerator;

    public function __construct(MailerService $mailer, UrlGeneratorInterface $urlGenerator)
    {
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CANDIDACY_CREATED => 'onCandidacyCreated',
            Events::CANDIDACY_REMOVED => 'onCandidacyRemoved',
        ];
    }

    public function onCandidacyCreated(CommitteeCandidacyEvent $event): void
    {
        $committee = $event->getCommittee();

        $this->mailer->sendMessage(CommitteeCandidacyCreatedConfirmationMessage::create(
            $event->getCandidate(),
            $committee->getCommitteeElection(),
            $this->urlGenerator->generate(
                'app_committee_show',
                [
                    'slug' => $committee->getSlug(),
                    'remove-candidacy' => true,
                    AnonymousFollowerSession::AUTHENTICATION_INTENTION => '/connexion',
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        ));

        $this->notifySupervisor($event, CommitteeNewCandidacyNotificationMessage::class);
    }

    public function onCandidacyRemoved(CommitteeCandidacyEvent $event): void
    {
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
                    $event->getCommitteeCandidacy(),
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
