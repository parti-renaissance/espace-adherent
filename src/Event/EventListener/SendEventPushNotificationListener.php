<?php

namespace App\Event\EventListener;

use App\Event\EventEvent;
use App\Event\EventRegistrationEvent;
use App\Events;
use App\JeMengage\Push\Command\EventCreationNotificationCommand;
use App\JeMengage\Push\Command\EventReferrerNotificationCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SendEventPushNotificationListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function notifyEventCreation(EventEvent $eventEvent): void
    {
        $event = $eventEvent->getEvent();

        $this->bus->dispatch(new EventCreationNotificationCommand($event->getUuid()));
    }

    public function notifyReferrer(EventRegistrationEvent $event): void
    {
        $eventRegistration = $event->getRegistration();

        if ($eventRegistration->referrer) {
            $this->bus->dispatch(new EventReferrerNotificationCommand($eventRegistration->getUuid()));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::EVENT_CREATED => ['notifyEventCreation', -2048],
            Events::EVENT_REGISTRATION_CREATED => ['notifyReferrer', -2048],
        ];
    }
}
