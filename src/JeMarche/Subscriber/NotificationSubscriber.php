<?php

namespace App\JeMarche\Subscriber;

use App\Entity\Event\CommitteeEvent;
use App\Entity\Event\DefaultEvent;
use App\Event\EventEvent;
use App\Events;
use App\JeMarche\JeMarcheDeviceNotifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotificationSubscriber implements EventSubscriberInterface
{
    private $deviceNotifier;

    public function __construct(JeMarcheDeviceNotifier $deviceNotifier)
    {
        $this->deviceNotifier = $deviceNotifier;
    }

    public function notifyEventCreation(EventEvent $eventEvent): void
    {
        $event = $eventEvent->getEvent();

        if ($event instanceof CommitteeEvent) {
            $this->deviceNotifier->sendCommitteeEventCreatedNotification($event);
        } elseif ($event instanceof DefaultEvent) {
            $this->deviceNotifier->sendDefaultEventCreatedNotification($event);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::EVENT_CREATED => ['notifyEventCreation', -2048],
        ];
    }
}
