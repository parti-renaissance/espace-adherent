<?php

declare(strict_types=1);

namespace App\Firebase\Notification;

interface TopicNotificationInterface extends NotificationInterface
{
    public function getTopic(): string;
}
