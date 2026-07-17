<?php

declare(strict_types=1);

namespace App\JeMengage\Push;

use App\Entity\NotificationObjectInterface;
use App\Firebase\Notification\NotificationInterface;
use App\JeMengage\Push\Command\AdherentMessageSentNotificationCommand;
use App\JeMengage\Push\Command\EventCreationNotificationCommand;
use App\JeMengage\Push\Command\EventLiveBeginNotificationCommand;
use App\JeMengage\Push\Command\EventReminderNotificationCommand;
use App\JeMengage\Push\Command\NationalEventTicketAvailableNotificationCommand;
use App\JeMengage\Push\Command\NewsCreatedNotificationCommand;
use App\JeMengage\Push\Command\NotifyEventRegistrantsCommand;
use App\JeMengage\Push\Command\NotifyForActionCommand;
use App\JeMengage\Push\Command\PollNotificationCommand;
use App\JeMengage\Push\Command\PrivateMessageNotificationCommand;
use App\JeMengage\Push\Command\PronosticNotificationCommand;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use App\JeMengage\Push\Notification\ActionBeginNotification;
use App\JeMengage\Push\Notification\ActionCancelledNotification;
use App\JeMengage\Push\Notification\ActionCreatedNotification;
use App\JeMengage\Push\Notification\ActionUpdatedNotification;
use App\JeMengage\Push\Notification\AdherentMessageSentNotification;
use App\JeMengage\Push\Notification\EventCancelledNotification;
use App\JeMengage\Push\Notification\EventCreatedNotification;
use App\JeMengage\Push\Notification\EventLiveBeginNotification;
use App\JeMengage\Push\Notification\EventReminderNotification;
use App\JeMengage\Push\Notification\EventUpdatedNotification;
use App\JeMengage\Push\Notification\NationalEventTicketNotification;
use App\JeMengage\Push\Notification\NewsCreatedNotification;
use App\JeMengage\Push\Notification\PollClosingNotification;
use App\JeMengage\Push\Notification\PollLaunchNotification;
use App\JeMengage\Push\Notification\PollReminderNotification;
use App\JeMengage\Push\Notification\PrivateMessageNotification;
use App\JeMengage\Push\Notification\PronosticCreationNotification;
use App\JeMengage\Push\Notification\PronosticReminderNotification;
use App\JeMengage\Push\Notification\PronosticResultNotification;
use App\JeMengage\Router;
use App\Poll\PollReminderTypeEnum;
use App\Pronostic\PronosticReminderTypeEnum;

class NotificationFactory
{
    public function __construct(private readonly Router $router)
    {
    }

    public function create(NotificationObjectInterface $object, SendNotificationCommandInterface $command): NotificationInterface
    {
        $notification = $this->initiateNotification($object, $command);
        $notification->addData('link', $this->router->generateLink($object));

        return $notification;
    }

    private function initiateNotification(NotificationObjectInterface $object, SendNotificationCommandInterface $command): NotificationInterface
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

        if ($command instanceof NotifyEventRegistrantsCommand) {
            return NotifyEventRegistrantsCommand::EVENT_CANCEL === $command->event
                ? EventCancelledNotification::create($object)
                : EventUpdatedNotification::create($object);
        }

        if ($command instanceof EventLiveBeginNotificationCommand) {
            return EventLiveBeginNotification::create($object);
        }

        if ($command instanceof EventCreationNotificationCommand) {
            return EventCreatedNotification::create($object);
        }

        if ($command instanceof EventReminderNotificationCommand) {
            return EventReminderNotification::create($object);
        }

        if ($command instanceof NewsCreatedNotificationCommand) {
            return NewsCreatedNotification::create($object);
        }

        if ($command instanceof NationalEventTicketAvailableNotificationCommand) {
            return NationalEventTicketNotification::create($object);
        }

        if ($command instanceof AdherentMessageSentNotificationCommand) {
            return AdherentMessageSentNotification::create($object);
        }

        if ($command instanceof PrivateMessageNotificationCommand) {
            return PrivateMessageNotification::create($object);
        }

        if ($command instanceof PronosticNotificationCommand) {
            return match ($command->type) {
                PronosticReminderTypeEnum::CREATION => PronosticCreationNotification::create($object),
                PronosticReminderTypeEnum::J_MINUS_1,
                PronosticReminderTypeEnum::H_MINUS_1,
                PronosticReminderTypeEnum::H_MINUS_5_MIN => PronosticReminderNotification::create($object, $command->type),
                PronosticReminderTypeEnum::RESULTS => PronosticResultNotification::create($object),
            };
        }

        if ($command instanceof PollNotificationCommand) {
            return match ($command->type) {
                PollReminderTypeEnum::LAUNCH => PollLaunchNotification::create($object),
                PollReminderTypeEnum::REMINDER_J1 => PollReminderNotification::create($object),
                PollReminderTypeEnum::CLOSING_H1 => PollClosingNotification::create($object),
            };
        }

        throw new \RuntimeException('[Notification] Command not supported');
    }
}
