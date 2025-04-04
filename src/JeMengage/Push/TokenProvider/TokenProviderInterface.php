<?php

namespace App\JeMengage\Push\TokenProvider;

use App\Entity\NotificationObjectInterface;
use App\Firebase\Notification\NotificationInterface;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;

interface TokenProviderInterface
{
    public static function getDefaultPriority(): int;

    public function supports(NotificationInterface $notification, NotificationObjectInterface $object): bool;

    public function getTokens(NotificationInterface $notification, NotificationObjectInterface $object, SendNotificationCommandInterface $command): array;
}
