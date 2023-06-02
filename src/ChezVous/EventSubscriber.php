<?php

namespace App\ChezVous;

use App\ChezVous\Command\DeleteMeasureTypeOnAlgoliaCommand;
use App\ChezVous\Command\UpdateMeasureTypeOnAlgoliaCommand;
use App\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EventSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    public function publishMeasureTypeUpdated(MeasureTypeEvent $event): void
    {
        $this->bus->dispatch(new UpdateMeasureTypeOnAlgoliaCommand($event->getMeasureType()->getId()));
    }

    public function publishMeasureTypeDeleted(MeasureTypeEvent $event): void
    {
        $this->bus->dispatch(new DeleteMeasureTypeOnAlgoliaCommand());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::CHEZVOUS_MEASURE_TYPE_UPDATED => [['publishMeasureTypeUpdated', -512]],
            Events::CHEZVOUS_MEASURE_TYPE_DELETED => [['publishMeasureTypeDeleted', -512]],
        ];
    }
}
