<?php

namespace App\Firebase\Notification;

interface NotificationInterface
{
    public function getTitle(): string;

    public function getBody(): string;
}
