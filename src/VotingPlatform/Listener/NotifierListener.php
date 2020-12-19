<?php

namespace App\VotingPlatform\Listener;

use App\VotingPlatform\Notifier\ElectionNotifier;
use App\VotingPlatform\Notifier\Event\CommitteeElectionCandidacyPeriodIsOverEvent;
use App\VotingPlatform\Notifier\Event\CommitteeElectionVoteReminderEvent;
use App\VotingPlatform\Notifier\Event\VotingPlatformElectionVoteIsOpenEvent;
use App\VotingPlatform\Notifier\Event\VotingPlatformElectionVoteIsOverEvent;
use App\VotingPlatform\Notifier\Event\VotingPlatformSecondRoundNotificationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotifierListener implements EventSubscriberInterface
{
    private $notifier;

    public function __construct(ElectionNotifier $notifier)
    {
        $this->notifier = $notifier;
    }

    public static function getSubscribedEvents()
    {
        return [
            CommitteeElectionCandidacyPeriodIsOverEvent::class => 'onCandidacyPeriodClose',
            CommitteeElectionVoteReminderEvent::class => 'onVoteRemind',

            VotingPlatformElectionVoteIsOpenEvent::class => 'onVoteOpen',
            VotingPlatformElectionVoteIsOverEvent::class => 'onVoteClose',
            VotingPlatformSecondRoundNotificationEvent::class => 'onVoteSecondRound',
        ];
    }

    public function onVoteOpen(VotingPlatformElectionVoteIsOpenEvent $event): void
    {
        $this->notifier->notifyElectionVoteIsOpen($event->getElection());
    }

    public function onCandidacyPeriodClose(CommitteeElectionCandidacyPeriodIsOverEvent $event): void
    {
        $this->notifier->notifyCommitteeElectionCandidacyPeriodIsOver(
            $event->getAdherent(),
            $event->getDesignation(),
            $event->getCommittee()
        );
    }

    public function onVoteRemind(CommitteeElectionVoteReminderEvent $event): void
    {
        $this->notifier->notifyCommitteeElectionVoteReminder(
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
}
