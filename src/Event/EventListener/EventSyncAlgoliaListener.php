<?php

namespace App\Event\EventListener;

use Algolia\SearchBundle\SearchService;
use App\Event\EventRegistrationEvent;
use App\Events;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSyncAlgoliaListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly SearchService $searchService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function onEventRegistrationCreated(EventRegistrationEvent $event): void
    {
        $this->searchService->index($this->entityManager, $event->getRegistration()->getEvent());
    }

    public static function getSubscribedEvents(): array
    {
        return [Events::EVENT_REGISTRATION_CREATED => 'onEventRegistrationCreated'];
    }
}
