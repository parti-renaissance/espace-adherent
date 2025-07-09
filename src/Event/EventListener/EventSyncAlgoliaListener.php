<?php

namespace App\Event\EventListener;

use App\Algolia\AlgoliaIndexedEntityManager;
use App\Event\EventRegistrationEvent;
use App\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSyncAlgoliaListener implements EventSubscriberInterface
{
    public function __construct(private readonly AlgoliaIndexedEntityManager $algoliaManager)
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
