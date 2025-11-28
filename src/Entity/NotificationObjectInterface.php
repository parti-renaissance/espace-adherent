<?php

namespace App\Entity;

use App\JeMengage\Push\Command\SendNotificationCommandInterface;

interface NotificationObjectInterface
{
    public function isNotificationEnabled(SendNotificationCommandInterface $command): bool;

    public function handleNotificationSent(SendNotificationCommandInterface $command): void;

    public function isNational(): bool;
}
