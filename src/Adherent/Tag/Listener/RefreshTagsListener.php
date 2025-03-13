<?php

namespace App\Adherent\Tag\Listener;

use App\Adherent\Tag\Command\AsyncRefreshAdherentTagCommand;
use App\Entity\Adherent;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\NationalEvent\Event\NationalEventInscriptionEventInterface;
use App\NationalEvent\Event\NewNationalEventInscriptionEvent;
use App\NationalEvent\Event\UpdateNationalEventInscriptionEvent;
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
            UpdateNationalEventInscriptionEvent::class => 'postEventInscription',
        ];
    }

    public function postEventInscription(NationalEventInscriptionEventInterface $event): void
    {
        if ($event->eventInscription->adherent) {
            $this->dispatch($event->eventInscription->adherent);
        }
    }

    public function updateAdherentTags(UserEvent $event): void
    {
        $this->dispatch($event->getAdherent());
    }

    private function dispatch(Adherent $adherent): void
    {
        $this->bus->dispatch(new AsyncRefreshAdherentTagCommand($adherent->getUuid()));
    }
}
