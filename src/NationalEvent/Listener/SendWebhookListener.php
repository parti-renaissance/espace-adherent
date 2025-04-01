<?php

namespace App\NationalEvent\Listener;

use App\NationalEvent\Command\SendWebhookCommand;
use App\NationalEvent\Event\NationalEventInscriptionEventInterface;
use App\NationalEvent\Event\NewNationalEventInscriptionEvent;
use App\NationalEvent\Event\UpdateNationalEventInscriptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SendWebhookListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NewNationalEventInscriptionEvent::class => ['sendWebhook', -100],
            UpdateNationalEventInscriptionEvent::class => ['sendWebhook', -100],
        ];
    }

    public function sendWebhook(NationalEventInscriptionEventInterface $event): void
    {
        $this->bus->dispatch(new SendWebhookCommand($event->eventInscription->getUuid()));
    }
}
