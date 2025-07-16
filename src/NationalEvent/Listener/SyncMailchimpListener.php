<?php

namespace App\NationalEvent\Listener;

use App\Mailchimp\Synchronisation\Command\NationalEventInscriptionChangeCommand;
use App\NationalEvent\Event\NationalEventInscriptionEventInterface;
use App\NationalEvent\Event\NewNationalEventInscriptionEvent;
use App\NationalEvent\Event\UpdateNationalEventInscriptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SyncMailchimpListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UpdateNationalEventInscriptionEvent::class => ['onInscriptionEdit', -1024],
            NewNationalEventInscriptionEvent::class => ['onInscriptionEdit', -1024],
        ];
    }

    public function onInscriptionEdit(NationalEventInscriptionEventInterface $event): void
    {
        $this->bus->dispatch(new NationalEventInscriptionChangeCommand($event->getEventInscription()->getUuid()));
    }
}
