<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Command;

use App\Entity\NotificationObjectInterface;
use Ramsey\Uuid\UuidInterface;

interface SendNotificationCommandInterface
{
    public function getUuid(): UuidInterface;

    public function getClass(): string;

    public function updateFromObject(NotificationObjectInterface $object): void;
}
