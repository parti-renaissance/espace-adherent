<?php

namespace App\Mailchimp\Synchronisation\EventListener;

use App\Committee\Event\CommitteeMergeEvent;
use App\Mailchimp\Synchronisation\Command\SyncAllMembersOfCommitteeCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SyncCommitteeAfterMergeListener implements EventSubscriberInterface
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public static function getSubscribedEvents()
    {
        return [
            CommitteeMergeEvent::class => 'onCommitteeMerge',
        ];
    }

    public function onCommitteeMerge(CommitteeMergeEvent $event): void
    {
        $this->bus->dispatch(new SyncAllMembersOfCommitteeCommand($event->getSourceCommittee()->getId()));
        $this->bus->dispatch(new SyncAllMembersOfCommitteeCommand($event->getDestinationCommittee()->getId()));
    }
}
