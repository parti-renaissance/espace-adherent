<?php

declare(strict_types=1);

namespace App\AdherentMessage\Listener;

use App\AdherentMessage\Command\InitiatePublicationReachFromPushCommand;
use App\Firebase\Event\PushNotificationSentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SavePublicationReachFromPushListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [PushNotificationSentEvent::class => 'onPushNotificationSentEvent'];
    }

    public function onPushNotificationSentEvent(PushNotificationSentEvent $event): void
    {
        if (!str_starts_with($event->notificationEntity->getScope(), 'publication:')) {
            return;
        }

        $this->bus->dispatch(new InitiatePublicationReachFromPushCommand($event->notificationEntity->getUuid()));
    }
}
