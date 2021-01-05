<?php

namespace App\VotingPlatform\Listener;

use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\Notifier\ElectionNotifier;
use App\VotingPlatform\Notifier\Event\CommitteeElectionCandidacyPeriodIsOverEvent;
use App\VotingPlatform\Notifier\Event\VotingPlatformElectionVoteIsOpenEvent;
use App\VotingPlatform\Notifier\Event\VotingPlatformElectionVoteIsOverEvent;
use App\VotingPlatform\Notifier\Event\VotingPlatformSecondRoundNotificationEvent;
use App\VotingPlatform\Notifier\Event\VotingPlatformVoteReminderEvent;
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

            VotingPlatformVoteReminderEvent::class => 'onVoteRemind',
            VotingPlatformElectionVoteIsOpenEvent::class => 'onVoteOpen',
            VotingPlatformElectionVoteIsOverEvent::class => 'onVoteClose',
            VotingPlatformSecondRoundNotificationEvent::class => 'onVoteSecondRound',
        ];
    }

    public function onVoteOpen(VotingPlatformElectionVoteIsOpenEvent $event): void
    {
        $election = $event->getElection();

        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR !== $election->getDesignationType()) {
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

    public function onVoteRemind(VotingPlatformVoteReminderEvent $event): void
    {
        $this->notifier->notifyVotingPlatformVoteReminder($event->getElection(), $event->getAdherent());
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
