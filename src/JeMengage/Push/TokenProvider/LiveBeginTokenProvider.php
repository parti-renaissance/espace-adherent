<?php

declare(strict_types=1);

namespace App\JeMengage\Push\TokenProvider;

use App\Entity\NotificationObjectInterface;
use App\Firebase\Notification\NotificationInterface;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use App\JeMengage\Push\Notification\EventLiveBeginNotification;

class LiveBeginTokenProvider extends AbstractTokenProvider
{
    public static function getDefaultPriority(): int
    {
        return 10;
    }

    public function supports(NotificationInterface $notification, NotificationObjectInterface $object): bool
    {
        return $notification instanceof EventLiveBeginNotification;
    }

    public function getTokens(NotificationInterface $notification, NotificationObjectInterface $object, SendNotificationCommandInterface $command): array
    {
        $notification->setScope('national');

        return $this->pushTokenRepository->findAllForNational();
    }
}
