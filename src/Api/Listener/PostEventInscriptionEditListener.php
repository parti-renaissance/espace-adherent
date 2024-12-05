<?php

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\NationalEvent\Command\SendWebhookCommand;
use App\NationalEvent\WebhookActionEnum;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

class PostEventInscriptionEditListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onEventInscriptionChange', EventPriorities::POST_WRITE]];
    }

    public function onEventInscriptionChange(RequestEvent $requestEvent): void
    {
        $request = $requestEvent->getRequest();

        if ('_api_/event_inscriptions/{uuid}{._format}_put' !== $request->attributes->get('_api_operation_name')) {
            return;
        }

        if (!($uuid = $request->attributes->get('uuid')) || !Uuid::isValid($uuid)) {
            return;
        }

        $this->bus->dispatch(new SendWebhookCommand(Uuid::fromString($uuid), WebhookActionEnum::POST_UPDATE));
    }
}
