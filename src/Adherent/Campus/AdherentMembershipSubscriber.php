<?php

namespace App\Adherent\Campus;

use App\Membership\AdherentEvents;
use App\Membership\Event\AdherentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AdherentMembershipSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function onRegistrationComplete(AdherentEvent $event): void
    {
        $this->bus->dispatch(new AdherentRegistrationCommand($event->getAdherent()->getUuid()));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AdherentEvents::REGISTRATION_COMPLETED => ['onRegistrationComplete', -256],
        ];
    }
}
