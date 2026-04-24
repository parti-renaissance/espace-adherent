<?php

declare(strict_types=1);

namespace App\Event\EventListener;

use App\Event\Command\InviteMembersForEventCommand;
use App\Event\EventEvent;
use App\Event\EventVisibilityEnum;
use App\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class InviteMembersOnEventCreatedListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    public function onEventCreated(EventEvent $event): void
    {
        if (EventVisibilityEnum::INVITATION !== $event->getEvent()->visibility) {
            return;
        }

        $this->messageBus->dispatch(new InviteMembersForEventCommand($event->getEvent()->getUuid()));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::EVENT_CREATED => ['onEventCreated', 1],
        ];
    }
}
