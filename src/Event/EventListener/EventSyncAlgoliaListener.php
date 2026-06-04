<?php

declare(strict_types=1);

namespace App\Event\EventListener;

use App\Algolia\AlgoliaIndexerInterface;
use App\Event\EventRegistrationEvent;
use App\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSyncAlgoliaListener implements EventSubscriberInterface
{
    public function __construct(private readonly AlgoliaIndexerInterface $algoliaManager)
    {
    }

    public function onEventRegistrationCreated(EventRegistrationEvent $event): void
    {
        $this->algoliaManager->postPersist($event->getRegistration()->getEvent());
    }

    public static function getSubscribedEvents(): array
    {
        return [Events::EVENT_REGISTRATION_CREATED => 'onEventRegistrationCreated'];
    }
}
