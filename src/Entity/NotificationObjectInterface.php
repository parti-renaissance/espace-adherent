<?php

namespace App\Entity;

use App\JeMarche\Command\SendNotificationCommandInterface;

interface NotificationObjectInterface
{
    public function isNotificationEnabled(SendNotificationCommandInterface $command): bool;

    public function handleNotificationSent(SendNotificationCommandInterface $command): void;
}
