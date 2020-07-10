<?php

namespace App\VotingPlatform\Notifier;

use App\VotingPlatform\Events;
use App\VotingPlatform\Notifier\Event\CommitteeElectionVoteIsOpenEvent;
use App\VotingPlatform\Notifier\Event\ElectionNotifyEventInterface;
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
        return [Events::VOTE_OPEN => 'onVoteOpen'];
    }

    public function onVoteOpen(ElectionNotifyEventInterface $event): void
    {
        if ($event instanceof CommitteeElectionVoteIsOpenEvent) {
            $this->notifier->notifyCommitteeElectionVoteIsOpen(
                $event->getAdherent(),
                $event->getElection(),
                $event->getCommittee()
            );
        }
    }
}
