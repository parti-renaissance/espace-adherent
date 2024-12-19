<?php

namespace App\JeMarche\Notification;

use App\Entity\NotificationObjectInterface;
use App\Firebase\Notification\NotificationInterface;
use App\JeMarche\Command\CommitteeEventCreationNotificationCommand;
use App\JeMarche\Command\DefaultEventCreationNotificationCommand;
use App\JeMarche\Command\EventReminderNotificationCommand;
use App\JeMarche\Command\NewsCreatedNotificationCommand;
use App\JeMarche\Command\NotifyForActionCommand;
use App\JeMarche\Command\SendNotificationCommandInterface;

class NotificationFactory
{
    public static function create(NotificationObjectInterface $object, SendNotificationCommandInterface $command): NotificationInterface
    {
        if ($command instanceof NotifyForActionCommand) {
            switch ($command->event) {
                case NotifyForActionCommand::EVENT_CREATE:
                    return ActionCreatedNotification::create($object);
                case NotifyForActionCommand::EVENT_UPDATE:
                    return ActionUpdatedNotification::create($object);
                case NotifyForActionCommand::EVENT_CANCEL:
                    return ActionCancelledNotification::create($object);
                case NotifyForActionCommand::EVENT_FIRST_NOTIFICATION:
                case NotifyForActionCommand::EVENT_SECOND_NOTIFICATION:
                    return ActionBeginNotification::create($object, NotifyForActionCommand::EVENT_FIRST_NOTIFICATION === $command->event);
            }
        }

        if ($command instanceof CommitteeEventCreationNotificationCommand) {
            return CommitteeEventCreatedNotification::create($object);
        }

        if ($command instanceof DefaultEventCreationNotificationCommand) {
            return DefaultEventCreatedNotification::create($object);
        }

        if ($command instanceof EventReminderNotificationCommand) {
            return EventReminderNotification::create($object);
        }

        if ($command instanceof NewsCreatedNotificationCommand) {
            return NewsCreatedNotification::create($object);
        }

        throw new \RuntimeException('[Notification] Command not supported');
    }
}
