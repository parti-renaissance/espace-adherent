<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Command;

use App\Entity\NotificationObjectInterface;
use Symfony\Component\Uid\Uuid;

interface SendNotificationCommandInterface
{
    public function getUuid(): Uuid;

    public function getClass(): string;

    public function updateFromObject(NotificationObjectInterface $object): void;
}
