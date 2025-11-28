<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Command;

use App\Entity\NotificationObjectInterface;
use App\Messenger\Message\UuidDefaultAsyncMessage;

abstract class AbstractSendNotificationCommand extends UuidDefaultAsyncMessage implements SendNotificationCommandInterface
{
    public function updateFromObject(NotificationObjectInterface $object): void
    {
    }
}
