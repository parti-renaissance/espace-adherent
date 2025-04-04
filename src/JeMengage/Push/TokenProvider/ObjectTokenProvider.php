<?php

namespace App\JeMengage\Push\TokenProvider;

use App\Entity\Action\Action;
use App\Entity\Event\Event;
use App\Entity\Jecoute\News;
use App\Entity\NationalEvent\NationalEvent;
use App\Entity\NotificationObjectInterface;
use App\Firebase\Notification\NotificationInterface;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use App\JeMengage\Push\Notification\ActionBeginNotification;
use App\JeMengage\Push\Notification\ActionCancelledNotification;
use App\JeMengage\Push\Notification\ActionUpdatedNotification;
use App\JeMengage\Push\Notification\EventCreatedNotification;
use App\JeMengage\Push\Notification\EventReminderNotification;
use App\JeMengage\Push\Notification\NationalEventTicketNotification;
use App\JeMengage\Push\Notification\NewsCreatedNotification;

class ObjectTokenProvider extends AbstractTokenProvider
{
    private const SUPPORTED_NOTIFICATIONS = [
        ActionBeginNotification::class,
        ActionCancelledNotification::class,
        ActionUpdatedNotification::class,
        EventCreatedNotification::class,
        EventReminderNotification::class,
        NewsCreatedNotification::class,
        NationalEventTicketNotification::class,
    ];

    public function supports(NotificationInterface $notification, NotificationObjectInterface $object): bool
    {
        return \in_array($notification::class, self::SUPPORTED_NOTIFICATIONS, true);
    }

    public function getTokens(NotificationInterface $notification, NotificationObjectInterface $object, SendNotificationCommandInterface $command): array
    {
        if (($object instanceof Event && $object->getCommittee()) || ($object instanceof News && $object->getCommittee())) {
            $notification->setScope('committee:'.$object->getCommittee()->getId());
        } else {
            $notification->setScope(match ($object::class) {
                Event::class => 'event:'.$object->getId(),
                Action::class => 'action:'.$object->getId(),
                NationalEvent::class => 'meeting:'.$object->getId(),
                default => null,
            });
        }

        return $this->pushTokenRepository->findAllForNotificationObject($object, $command);
    }

    public static function getDefaultPriority(): int
    {
        return -100;
    }
}
