<?php

declare(strict_types=1);

namespace App\Firebase\Notification;

interface MulticastNotificationInterface extends NotificationInterface
{
    public function getTokens(): array;
}
