<?php

namespace App\Firebase\Notification;

interface MulticastNotificationInterface extends NotificationInterface
{
    public function getTokens(): array;
}
