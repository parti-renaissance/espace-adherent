<?php

namespace App\Adherent\Tag\Listener;

use App\Adherent\Tag\Command\RefreshAdherentTagCommand;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class RefreshTagsListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_MEMBERSHIP_COMPLETED => 'onUserCreation',
            UserEvents::USER_VALIDATED => 'onUserCreation',
        ];
    }

    public function onUserCreation(UserEvent $event): void
    {
        $this->bus->dispatch(new RefreshAdherentTagCommand($event->getUser()->getUuid()));
    }
}
