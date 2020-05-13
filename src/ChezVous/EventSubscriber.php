<?php

namespace App\ChezVous;

use App\Events;
use App\Producer\ChezVous\AlgoliaProducerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSubscriber implements EventSubscriberInterface
{
    private $producer;

    public function __construct(AlgoliaProducerInterface $producer)
    {
        $this->producer = $producer;
    }

    public function publishMeasureTypeUpdated(MeasureTypeEvent $event): void
    {
        $this->producer->dispatchMeasureTypeUpdated($event->getMeasureType());
    }

    public function publishMeasureTypeDeleted(MeasureTypeEvent $event): void
    {
        $this->producer->dispatchMeasureTypeDeleted($event->getMeasureType());
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CHEZVOUS_MEASURE_TYPE_UPDATED => [['publishMeasureTypeUpdated', -512]],
            Events::CHEZVOUS_MEASURE_TYPE_DELETED => [['publishMeasureTypeDeleted', -512]],
        ];
    }
}
