<?php

namespace App\Event\EventListener;

use App\Entity\Event\CommitteeEvent;
use App\Entity\Event\DefaultEvent;
use App\Event\EventEvent;
use App\Events;
use App\JeMengage\Push\Command\CommitteeEventCreationNotificationCommand;
use App\JeMengage\Push\Command\DefaultEventCreationNotificationCommand;
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

        if ($event instanceof CommitteeEvent) {
            $this->bus->dispatch(new CommitteeEventCreationNotificationCommand($event->getUuid()));
        } elseif ($event instanceof DefaultEvent) {
            $this->bus->dispatch(new DefaultEventCreationNotificationCommand($event->getUuid()));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::EVENT_CREATED => ['notifyEventCreation', -2048],
        ];
    }
}
