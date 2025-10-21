<?php

namespace App\Firebase\Event;

use App\Entity\Notification;
use Symfony\Contracts\EventDispatcher\Event;

class PushNotificationSentEvent extends Event
{
    public function __construct(public readonly Notification $notificationEntity)
    {
    }
}
