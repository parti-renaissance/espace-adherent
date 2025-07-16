<?php

namespace App\NationalEvent\Listener;

use App\NationalEvent\Event\NewNationalEventInscriptionEvent;
use App\NationalEvent\Notifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendDuplicationEmailListener implements EventSubscriberInterface
{
    public function __construct(private readonly Notifier $notifier)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [NewNationalEventInscriptionEvent::class => 'onNewInscription'];
    }

    public function onNewInscription(NewNationalEventInscriptionEvent $event): void
    {
        $eventInscription = $event->eventInscription;

        if ($eventInscription->event->isCampus() && $eventInscription->isDuplicate() && $eventInscription->originalInscription) {
            $this->notifier->sendDuplicateNotification($eventInscription->originalInscription);
        }
    }
}
