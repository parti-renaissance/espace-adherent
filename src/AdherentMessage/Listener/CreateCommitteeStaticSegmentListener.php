<?php

namespace AppBundle\AdherentMessage\Listener;

use AppBundle\AdherentMessage\Command\CreateCommitteeStaticSegmentCommand;
use AppBundle\Committee\CommitteeEvent;
use AppBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateCommitteeStaticSegmentListener implements EventSubscriberInterface
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::COMMITTEE_UPDATED => 'onCommitteeUpdate',
        ];
    }

    public function onCommitteeUpdate(CommitteeEvent $event): void
    {
        $committee = $event->getCommittee();

        if (!$committee->isApproved() || $committee->getMailchimpId()) {
            return;
        }

        $this->bus->dispatch(new CreateCommitteeStaticSegmentCommand($committee->getUuid()));
    }
}
