<?php

declare(strict_types=1);

namespace App\Adhesion\Listener;

use App\Adherent\Command\UpdateAdherentLinkCommand;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateAdherentLinkListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function onUserCreated(UserEvent $event): void
    {
        $this->bus->dispatch(new UpdateAdherentLinkCommand($event->getAdherent()->getUuid()));
    }

    public static function getSubscribedEvents(): array
    {
        return [UserEvents::USER_CREATED => ['onUserCreated', -257]];
    }
}
