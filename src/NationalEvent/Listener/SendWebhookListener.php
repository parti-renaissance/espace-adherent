<?php

namespace App\NationalEvent\Listener;

use App\NationalEvent\Command\SendWebhookCommand;
use App\NationalEvent\NewNationalEventInscriptionEvent;
use App\NationalEvent\WebhookActionEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SendWebhookListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [NewNationalEventInscriptionEvent::class => ['sendWebhook', -100]];
    }

    public function sendWebhook(NewNationalEventInscriptionEvent $event): void
    {
        $this->bus->dispatch(new SendWebhookCommand($event->eventInscription->getUuid(), WebhookActionEnum::POST_CREATE));
    }
}
