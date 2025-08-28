<?php

namespace App\Event\EventListener;

use App\Event\Command\SendReferrerPrivateMessageCommand;
use App\Event\EventRegistrationEvent;
use App\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SendReferrerPrivateMessageListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    public function notifyReferrer(EventRegistrationEvent $event): void
    {
        $eventRegistration = $event->getRegistration();

        if (!$eventRegistration->referrer) {
            return;
        }

        $this->messageBus->dispatch(new SendReferrerPrivateMessageCommand($eventRegistration->getUuid()));
    }

    public static function getSubscribedEvents(): array
    {
        return [Events::EVENT_REGISTRATION_CREATED => ['notifyReferrer', -2048]];
    }
}
