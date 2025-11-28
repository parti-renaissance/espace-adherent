<?php

declare(strict_types=1);

namespace App\Adhesion\Listener;

use App\Adhesion\Command\SendNewPrimoCotisationNotificationCommand;
use App\Adhesion\Events\NewCotisationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class SendNewPrimoCotisationNotificationListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NewCotisationEvent::class => 'sendNotification',
        ];
    }

    public function sendNotification(NewCotisationEvent $event): void
    {
        $this->bus->dispatch(new SendNewPrimoCotisationNotificationCommand(
            $event->getAdherent()->getUuid(),
            $event->donation->getAmountInEuros()
        ), [new DelayStamp(900000)]); // wait 15 minutes before sending the notification
    }
}
