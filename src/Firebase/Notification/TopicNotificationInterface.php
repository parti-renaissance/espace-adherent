<?php

namespace App\Firebase\Notification;

interface TopicNotificationInterface extends NotificationInterface
{
    public function getTopic(): string;
}
