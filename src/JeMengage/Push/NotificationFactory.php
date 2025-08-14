<?php

namespace App\JeMengage\Push;

use App\Entity\NotificationObjectInterface;
use App\Firebase\Notification\NotificationInterface;
use App\JeMengage\Router;

class NotificationFactory
{
    public function __construct(private readonly Router $router)
    {
    }

    public function create(NotificationObjectInterface $object, Command\SendNotificationCommandInterface $command): NotificationInterface
    {
        $notification = $this->initiateNotification($object, $command);
        $notification->addData('link', $this->router->generateLink($object));

        return $notification;
    }

    private function initiateNotification(NotificationObjectInterface $object, Command\SendNotificationCommandInterface $command): NotificationInterface
    {
        if ($command instanceof Command\NotifyForActionCommand) {
            switch ($command->event) {
                case Command\NotifyForActionCommand::EVENT_CREATE:
                    return Notification\ActionCreatedNotification::create($object);
                case Command\NotifyForActionCommand::EVENT_UPDATE:
                    return Notification\ActionUpdatedNotification::create($object);
                case Command\NotifyForActionCommand::EVENT_CANCEL:
                    return Notification\ActionCancelledNotification::create($object);
                case Command\NotifyForActionCommand::EVENT_FIRST_NOTIFICATION:
                case Command\NotifyForActionCommand::EVENT_SECOND_NOTIFICATION:
                    return Notification\ActionBeginNotification::create($object, Command\NotifyForActionCommand::EVENT_FIRST_NOTIFICATION === $command->event);
            }
        }

        if ($command instanceof Command\EventCreationNotificationCommand) {
            return Notification\EventCreatedNotification::create($object);
        }

        if ($command instanceof Command\EventReminderNotificationCommand) {
            return Notification\EventReminderNotification::create($object);
        }

        if ($command instanceof Command\EventLiveBeginNotificationCommand) {
            return Notification\EventLiveBeginNotification::create($object);
        }

        if ($command instanceof Command\NewsCreatedNotificationCommand) {
            return Notification\NewsCreatedNotification::create($object);
        }

        if ($command instanceof Command\NationalEventTicketAvailableNotificationCommand) {
            return Notification\NationalEventTicketNotification::create($object);
        }

        if ($command instanceof Command\AdherentMessageSentNotificationCommand) {
            return Notification\AdherentMessageSentNotification::create($object);
        }

        throw new \RuntimeException('[Notification] Command not supported');
    }
}
