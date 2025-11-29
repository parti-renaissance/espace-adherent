<?php

declare(strict_types=1);

namespace App\VotingPlatform\Listener;

use App\VotingPlatform\Election\Event\NewVote;
use App\VotingPlatform\Notifier\ElectionNotifier;
use App\VotingPlatform\Notifier\Event\CommitteeElectionCandidacyPeriodIsOverEvent;
use App\VotingPlatform\Notifier\Event\VotingPlatformElectionVoteIsOpenEvent;
use App\VotingPlatform\Notifier\Event\VotingPlatformElectionVoteIsOverEvent;
use App\VotingPlatform\Notifier\Event\VotingPlatformSecondRoundNotificationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotifierListener implements EventSubscriberInterface
{
    public function __construct(private readonly ElectionNotifier $notifier)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommitteeElectionCandidacyPeriodIsOverEvent::class => 'onCandidacyPeriodClose',

            VotingPlatformElectionVoteIsOpenEvent::class => 'onVoteOpen',
            VotingPlatformElectionVoteIsOverEvent::class => 'onVoteClose',
            VotingPlatformSecondRoundNotificationEvent::class => 'onVoteSecondRound',
            NewVote::class => 'onVoteCreated',
        ];
    }

    public function onVoteOpen(VotingPlatformElectionVoteIsOpenEvent $event): void
    {
        $election = $event->getElection();
        $designation = $election->getDesignation();

        if (!$designation->isCommitteeSupervisorType() || $election->getDesignation()->isLimited()) {
            $this->notifier->notifyElectionVoteIsOpen($election);
        }
    }

    public function onCandidacyPeriodClose(CommitteeElectionCandidacyPeriodIsOverEvent $event): void
    {
        $this->notifier->notifyCommitteeElectionCandidacyPeriodIsOver(
            $event->getAdherent(),
            $event->getDesignation(),
            $event->getCommittee()
        );
    }

    public function onVoteClose(VotingPlatformElectionVoteIsOverEvent $event): void
    {
        $this->notifier->notifyElectionVoteIsOver($event->getElection());
    }

    public function onVoteSecondRound(VotingPlatformSecondRoundNotificationEvent $event): void
    {
        $this->notifier->notifyElectionSecondRound($event->getElection());
    }

    public function onVoteCreated(NewVote $event): void
    {
        $this->notifier->notifyVoteConfirmation($event->election, $event->voter, $event->voterKey);
    }
}
