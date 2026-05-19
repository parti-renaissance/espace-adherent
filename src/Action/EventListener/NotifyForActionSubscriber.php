<?php

declare(strict_types=1);

namespace App\Action\EventListener;

use App\Action\ActionEvent;
use App\Events;
use App\JeMengage\Push\Command\NotifyForActionCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class NotifyForActionSubscriber implements EventSubscriberInterface
{
    private const array EVENT_MAP = [
        Events::ACTION_CREATED => NotifyForActionCommand::EVENT_CREATE,
        Events::ACTION_UPDATED => NotifyForActionCommand::EVENT_UPDATE,
        Events::ACTION_CANCELLED => NotifyForActionCommand::EVENT_CANCEL,
    ];

    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::ACTION_CREATED => 'onActionEvent',
            Events::ACTION_UPDATED => 'onActionEvent',
            Events::ACTION_CANCELLED => 'onActionEvent',
        ];
    }

    public function onActionEvent(ActionEvent $event, string $eventName): void
    {
        $this->messageBus->dispatch(new NotifyForActionCommand(
            $event->getAction()->getUuid(),
            self::EVENT_MAP[$eventName],
        ));
    }
}
