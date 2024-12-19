<?php

namespace App\JeMarche\Command;

use App\Entity\NotificationObjectInterface;

interface SendNotificationCommandInterface
{
    public function getClass(): string;

    public function updateFromObject(NotificationObjectInterface $object): void;
}
