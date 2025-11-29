<?php

declare(strict_types=1);

namespace App\NationalEvent\Listener;

use App\NationalEvent\Command\GenerateTicketQRCodeCommand;
use App\NationalEvent\Event\NewNationalEventInscriptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class GenerateQRCodeListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [NewNationalEventInscriptionEvent::class => 'generateQRCode'];
    }

    public function generateQRCode(NewNationalEventInscriptionEvent $event): void
    {
        $this->bus->dispatch(new GenerateTicketQRCodeCommand($event->eventInscription->getUuid()));
    }
}
