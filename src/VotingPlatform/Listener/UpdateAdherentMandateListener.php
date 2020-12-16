<?php

namespace App\VotingPlatform\Listener;

use App\VotingPlatform\Command\UpdateMandateForElectedAdherentCommand;
use App\VotingPlatform\Notifier\Event\VotingPlatformElectionVoteIsOverEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateAdherentMandateListener implements EventSubscriberInterface
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public static function getSubscribedEvents()
    {
        return [
            VotingPlatformElectionVoteIsOverEvent::class => ['onVoteClose', -256],
        ];
    }

    public function onVoteClose(VotingPlatformElectionVoteIsOverEvent $event): void
    {
        $this->bus->dispatch(new UpdateMandateForElectedAdherentCommand($event->getElection()->getUuid()));
    }
}
