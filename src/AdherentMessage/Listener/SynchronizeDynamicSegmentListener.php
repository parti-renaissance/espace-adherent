<?php

namespace App\AdherentMessage\Listener;

use App\AdherentMessage\Command\SynchronizeDynamicSegmentCommand;
use App\AdherentMessage\Segment\DynamicSegmentEvent;
use App\AdherentMessage\Segment\DynamicSegmentEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SynchronizeDynamicSegmentListener implements EventSubscriberInterface
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public static function getSubscribedEvents()
    {
        return [
            DynamicSegmentEvents::POST_CHANGE => 'onSegmentChange',
        ];
    }

    public function onSegmentChange(DynamicSegmentEvent $event): void
    {
        if (!($segment = $event->getSegment())->isSynchronized()) {
            $this->bus->dispatch(new SynchronizeDynamicSegmentCommand($segment->getUuid(), \get_class($segment)));
        }
    }
}
