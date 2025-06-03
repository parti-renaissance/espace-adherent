<?php

namespace App\Agora\Event;

use App\Agora\Command\InviteAgoraMembersForEventCommand;
use App\Event\EventEvent;
use App\Event\EventVisibilityEnum;
use App\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class InviteAgoraMembersOnEventCreationListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    public function onEventCreated(EventEvent $event): void
    {
        if (EventVisibilityEnum::INVITATION_AGORA === $event->getEvent()->visibility) {
            $this->messageBus->dispatch(new InviteAgoraMembersForEventCommand($event->getEvent()->getUuid()));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [Events::EVENT_CREATED => 'onEventCreated'];
    }
}
