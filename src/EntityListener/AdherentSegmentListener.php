<?php

namespace App\EntityListener;

use App\AdherentMessage\Command\CreateStaticSegmentCommand;
use App\AdherentMessage\Command\SynchronizeAdherentSegmentCommand;
use App\Entity\AdherentSegment;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;

class AdherentSegmentListener
{
    private $bus;
    private $segmentToSync;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function postPersist(AdherentSegment $segment): void
    {
        $this->bus->dispatch(new CreateStaticSegmentCommand($segment->getUuid(), $segment::class));
    }

    public function preUpdate(AdherentSegment $segment, PreUpdateEventArgs $event): void
    {
        if ($event->hasChangedField('memberIds') || $event->hasChangedField('mailchimpId')) {
            $this->segmentToSync = $segment;
        }
    }

    public function postUpdate(AdherentSegment $segment): void
    {
        if ($this->segmentToSync === $segment && $segment->getMailchimpId()) {
            $this->bus->dispatch(new SynchronizeAdherentSegmentCommand($segment->getId()));

            $this->segmentToSync = null;
        }
    }
}
