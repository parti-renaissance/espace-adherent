<?php

namespace App\JeMarche\Subscriber;

use App\Event\EventEvent;
use App\Events;
use App\JeMarche\EventCreationNotificationCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class NotificationSubscriber implements EventSubscriberInterface
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function notifyEventCreation(EventEvent $eventEvent): void
    {
        $event = $eventEvent->getEvent();
        if (!$event) {
            return;
        }

        $this->bus->dispatch(new EventCreationNotificationCommand($event->getUuid()));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::EVENT_CREATED => ['notifyEventCreation', -128],
        ];
    }
}
