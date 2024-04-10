<?php

namespace App\Adherent\Tag\Listener;

use App\Adherent\Tag\Command\RefreshAdherentTagCommand;
use App\Entity\Adherent;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\NationalEvent\NewNationalEventInscriptionEvent;
use App\Procuration\V2\Event\NewProcurationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class RefreshTagsListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_CREATED => 'updateAdherentTags',
            UserEvents::USER_VALIDATED => 'updateAdherentTags',
            UserEvents::USER_UPDATED_IN_ADMIN => 'updateAdherentTags',

            NewNationalEventInscriptionEvent::class => 'postEventInscription',
            NewProcurationEvent::class => 'postProcurationHandle',
        ];
    }

    public function postEventInscription(NewNationalEventInscriptionEvent $event): void
    {
        if ($event->eventInscription->adherent) {
            $this->dispatch($event->eventInscription->adherent);
        }
    }

    public function postProcurationHandle(NewProcurationEvent $event): void
    {
        if ($adherent = $event->procuration->adherent) {
            $this->dispatch($adherent);
        }
    }

    public function updateAdherentTags(UserEvent $event): void
    {
        $this->dispatch($event->getUser());
    }

    private function dispatch(Adherent $adherent): void
    {
        $this->bus->dispatch(new RefreshAdherentTagCommand($adherent->getUuid()));
    }
}
