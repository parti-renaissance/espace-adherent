<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Notification;

use App\Entity\Event\Event;
use App\Firebase\Notification\AbstractMulticastNotification;

class EventLiveBeginNotification extends AbstractMulticastNotification
{
    public static function create(Event $event): self
    {
        return new self('🔴 On est en direct !', $event->getName());
    }
}
