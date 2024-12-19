<?php

namespace App\JeMarche\Command;

use App\Entity\NotificationObjectInterface;
use App\Messenger\Message\UuidDefaultAsyncMessage;

abstract class AbstractSendNotificationCommand extends UuidDefaultAsyncMessage implements SendNotificationCommandInterface
{
    public function updateFromObject(NotificationObjectInterface $object): void
    {
    }
}
