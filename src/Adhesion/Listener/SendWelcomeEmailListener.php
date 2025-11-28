<?php

declare(strict_types=1);

namespace App\Adhesion\Listener;

use App\Adhesion\Command\SendWelcomeEmailCommand;
use App\Adhesion\Events\NewCotisationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class SendWelcomeEmailListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NewCotisationEvent::class => 'sendWelcomeEmail',
        ];
    }

    public function sendWelcomeEmail(NewCotisationEvent $event): void
    {
        $this->bus->dispatch(
            new SendWelcomeEmailCommand($event->getAdherent()->getUuid(), $event->donation->isReAdhesion()),
            [new DelayStamp(600000)] // 10 minutes delay
        );
    }
}
