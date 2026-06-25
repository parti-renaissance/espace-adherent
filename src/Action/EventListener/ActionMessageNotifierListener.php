<?php

declare(strict_types=1);

namespace App\Action\EventListener;

use App\Action\ActionEvent;
use App\Action\Command\SendActionCreationNotificationCommand;
use App\Action\Command\SendActionParticipantsNotificationCommand;
use App\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ActionMessageNotifierListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::ACTION_CREATED => 'onActionCreated',
            Events::ACTION_UPDATED => 'onActionUpdated',
            Events::ACTION_CANCELLED => 'onActionCancelled',
        ];
    }

    public function onActionCreated(ActionEvent $event): void
    {
        $this->bus->dispatch(new SendActionCreationNotificationCommand($event->getAction()->getUuid()));
    }

    public function onActionUpdated(ActionEvent $event): void
    {
        $this->bus->dispatch(new SendActionParticipantsNotificationCommand($event->getAction()->getUuid()));
    }

    public function onActionCancelled(ActionEvent $event): void
    {
        $this->bus->dispatch(new SendActionParticipantsNotificationCommand($event->getAction()->getUuid(), true));
    }
}
