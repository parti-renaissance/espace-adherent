<?php

namespace App\AdherentMessage\Listener;

use App\AdherentMessage\Command\CreateStaticSegmentCommand;
use App\AdherentMessage\StaticSegmentInterface;
use App\Committee\CommitteeEvent;
use App\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateStaticSegmentListener implements EventSubscriberInterface
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
        if (($committee = $event->getCommittee())->isApproved()) {
            $this->onUpdate($committee);
        }
    }

    private function onUpdate(StaticSegmentInterface $object): void
    {
        if (!$object->getMailchimpId()) {
            $this->bus->dispatch(new CreateStaticSegmentCommand($object->getUuid(), \get_class($object)));
        }
    }
}
