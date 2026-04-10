<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Listener;

use App\Firebase\Event\PushNotificationSentEvent;
use App\Firebase\PushNotificationStatusEnum;
use App\JeMengage\Push\Command\LinkNotificationPushTokensCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class LinkPushTokensOnDeliveredListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [PushNotificationSentEvent::class => 'onPushNotificationSent'];
    }

    public function onPushNotificationSent(PushNotificationSentEvent $event): void
    {
        $pushNotification = $event->notificationEntity->pushNotification;

        if (!$pushNotification || PushNotificationStatusEnum::DELIVERED !== $pushNotification->status) {
            return;
        }

        $this->bus->dispatch(new LinkNotificationPushTokensCommand($pushNotification->getUuid()));
    }
}
